<?php


class LimeSoda_Cashpresso_Model_Api_Client extends LimeSoda_Cashpresso_Model_Api_Abstract
{
    const METHOD_PARTNER_INFO = 'partnerInfo';
    const METHOD_BUY = 'buy';
    const METHOD_SIMULATION = 'simulation/callback';

    const CODE_SIMULATION_SUCCESS = 'SUCCESS';
    const CODE_SIMULATION_CANCEL = 'CANCELLED';
    const CODE_SIMULATION_TIMEOUT = 'TIMEOUT';

    protected $_post = array();

    /**
     * @param bool $showError
     * @return bool
     * @throws Zend_Http_Client_Exception
     */
    public function getPartnerInfo($showError = true)
    {
        $request = $this->getRequest(self::METHOD_PARTNER_INFO);

        $response = $request->request();

        if ($response->isSuccessful()) {
            $respond = Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)) {
                return $this->handleRespond($respond, $showError);
            }
        }

        $message = 'Cashpresso getPartnerInfo error: ' . $response->getMessage();

        if ($showError){
            $this->getSession()->addError($message);
        } else {
            Mage::log($message, Zend_Log::ERR);
        }
    }

    /**
     * @param $order Mage_Sales_Model_Order
     * @return mixed
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     */
    public function sendOrder($order)
    {
        $request = $this->getRequest(self::METHOD_BUY);

        list($locale) = explode('_', strtolower(Mage::app()->getLocale()->getLocaleCode()));

        $price = round($order->getGrandTotal(), 2);

        $additionalData = json_decode($order->getPayment()->getAdditionalData());
        $data = array(
            'partnerApiKey' => $this->getPartnerApiKey(),
            'c2EcomId' => isset($additionalData['cashpresso']['token']) ? $additionalData['cashpresso']['token'] : null,
            'amount' => $price,
            'validUntil' => $this->_helper()->getTimeout(),
            'bankUsage' => $order->getIncrementId(),
            'interestFreeDaysMerchant' => $this->_helper()->getInterestFreeDay(),
            'language' => $locale,
            'callbackUrl' => Mage::getUrl('cashpresso/api/callback', array('_secure' => true))
        );

        if (!empty($account = $this->_helper()->getTargetAccount())) {
            $data['targetAccountId'] = $account;
        }

        $data['verificationHash'] = hash('sha512', $this->getHash($price, $order->getIncrementId(), $account));

        if ($customerID = Mage::getModel('customer/session')->getCustomer()->getId()) {
            $data['merchantCustomerId'] = $customerID;
        }

        if ($address = $order->getBillingAddress()) {
            $billingAddress = array(
                "country" => $address->getCountryId(),
                "zip" => $address->getPostcode(),
                "city" => $address->getCity(),
                "street" => str_replace("\n", ", ", $address->getStreetFull())
            );

            $data['invoiceAddress'] = $billingAddress;
        }

        if ($address = $order->getShippingAddress()) {
            $shippingAddress = array(
                "country" => $address->getCountryId(),
                "zip" => $address->getPostcode(),
                "city" => $address->getCity(),
                "street" => str_replace("\n", ", ", $address->getStreetFull())
            );

            $data['deliveryAddress'] = $shippingAddress;
        }

        $items = $order->getAllItems();

        $cart = array();

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($items as $item) {
            $cart[] = array(
                'description' => $item->getName(),
                'amount' => $item->getQtyOrdered()
            );
        }

        if ($this->_helper()->isDebugEnabled()) {
            Mage::log(print_r($data, true), Zend_Log::DEBUG, 'debug.log');
        }

        $request->setMethod(Varien_Http_Client::POST);
        $request->setRawData(Mage::helper('core')->jsonEncode($data), 'application/json');

        $response = $request->request();
        if ($this->_helper()->isDebugEnabled()) {
            Mage::log($response->getBody(), Zend_Log::DEBUG, 'debug.log');
        }


        if ($response->isSuccessful()) {
            $respond = Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)) {

                $respond = $this->handleRespond($respond);

                if (empty($respond['purchaseId'])) {
                    $this->getSession()->addError($this->_helper()->__("Cashpresso: purchaseId is empty"));

                    $purchaseId = null;
                } else {
                    $purchaseId = $respond['purchaseId'];
                }

                return $purchaseId;
            }
        }

        $this->getSession()->addError("Cashpresso order request error: " . $response->getMessage());
    }

    /**
     * @param $amount
     * @param $bankUsage
     * @param string $targetAccountId
     * @return string
     */
    public function getHash($amount, $bankUsage, $targetAccountId = '')
    {
        return $this->getSecretKey() . ';' . ($amount * 100) . ';' . $this->_helper()->getInterestFreeDay() . ';' . $bankUsage . ';' . $targetAccountId;
    }

    /**
     * @param $code
     * @param $purchaseID
     * @return bool
     * @throws Exception
     */
    public function sendSimulationCallbackRequest($code, $purchaseID)
    {
        if (!in_array($code, array(self::CODE_SIMULATION_CANCEL, self::CODE_SIMULATION_SUCCESS, self::CODE_SIMULATION_TIMEOUT))) {
            throw new Exception(Mage::helper('ls_cashpresso')->__("Wrong parameter for sendSimulationCallbackRequest"));
        }

        $request = $this->getRequest(self::METHOD_SIMULATION);

        $data = array(
            'partnerApiKey' => $this->getPartnerApiKey(),
            'purchaseId' => $purchaseID,
            'type' => $code
        );

        $request->setMethod(Varien_Http_Client::POST);
        $request->setRawData(Mage::helper('core')->jsonEncode($data), 'application/json');

        $response = $request->request();

        if ($response->isSuccessful()) {
            $respond = Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)) {

                $respond = $this->handleRespond($respond);

                if (empty($respond['purchaseId'])) {
                    $this->getSession()->addError($this->_helper()->__("Cashpresso: purchaseId is empty"));

                    $respond = null;
                }

                if ($respond !== false) {
                    return;
                }
            }
        }

        $errors = implode("\n", $this->getSession()->getMessages(true));

        throw new Exception($errors);
    }
}