<?php
/**
 * 14.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Api.php
 */

class Limesoda_Cashpresso_Model_Api_Client extends Limesoda_Cashpresso_Model_Api_Abstract
{
    const METHOD_PARTNER_INFO = 'partnerInfo';
    const METHOD_BUY = 'buy';
    const METHOD_SIMULATION = 'simulation/callback';

    const CODE_SIMULATION_SUCCESS = 'SUCCESS';
    const CODE_SIMULATION_CANCEL = 'CANCELLED';
    const CODE_SIMULATION_TIMEOUT = 'TIMEOUT';

    protected $_post = array();

    /**
     * @return mixed
     * @throws Exception
     */
    public function getPartnerInfo()
    {
        $request = $this->getRequest(self::METHOD_PARTNER_INFO);

        $response = $request->request();

        if ($response->isSuccessful()) {
            $respond = Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)) {
                if (empty($respond['success'])) {
                    throw new Mage_Core_Exception(Mage::helper('ls_cashpresso')->__($respond['error']['description']));
                }

                return $respond;
            }
        }

        throw new Mage_Core_Exception(Mage::helper('ls_cashpresso')->__($response->getMessage()));
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

        $data = array(
            'partnerApiKey' => $this->getPartnerApiKey(),
            'c2EcomId' => $order->getPayment()->getAdditionalData(),
            'amount' => $order->getGrandTotal(),
            'verificationHash' => hash('sha512', $this->getHash($order->getGrandTotal(), $order->getIncrementId())),
            'validUntil' => $this->_helper()->getTimeout(),
            'bankUsage' => $order->getIncrementId(),
            'interestFreeDaysMerchant' => $this->_helper()->getInterestFreeDay(),
            'description' => $this->_helper()->getDescription(),
            'language' => $locale,
            'callbackUrl' => Mage::getUrl('cashpresso/api/callback',  array('_secure'=>true))
        );

        if ($customerID = Mage::getModel('customer/session')->getCustomer()->getId()) {
            $data['merchantCustomerId'] = $customerID;
        }

        if ($address = $order->getBillingAddress()) {
            $billingAddress = array(
                "country" => $address->getCountryId(),
                "zip" => $address->getPostcode(),
                "city" => $address->getCity(),
                "street" => isset($address->getStreet()[0]) ? $address->getStreet()[0] : ''
                //"housenumber" : $ad,
            );

            $data['invoiceAddress'] = $billingAddress;
        }

        if ($address = $order->getShippingAddress()) {
            $shippingAddress = array(
                "country" => $address->getCountryId(),
                "zip" => $address->getPostcode(),
                "city" => $address->getCity(),
                "street" => isset($address->getStreet()[0]) ? $address->getStreet()[0] : ''
                //"housenumber" : $ad,
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

        Mage::log(print_r($data, true), Zend_Log::DEBUG, 'debug.log');

        $request->setMethod(Varien_Http_Client::POST);
        $request->setRawData(json_encode($data), 'application/json');

        $response = $request->request();
        Mage::log($response->getBody(), Zend_Log::DEBUG, 'debug.log');

        if ($response->isSuccessful()) {
            $respond = Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)) {
                if (empty($respond['success']) || empty($respond['purchaseId'])) {
                    throw new Mage_Payment_Model_Info_Exception(Mage::helper('ls_cashpresso')->__($respond['error']['description']));
                }

                return $respond['purchaseId'];
            }
        }
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
     * @return mixed
     * @throws Exception
     * @throws Mage_Payment_Model_Info_Exception
     * @throws Zend_Http_Client_Exception
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
        $request->setRawData(json_encode($data), 'application/json');

        $response = $request->request();

        if ($response->isSuccessful()) {
            $respond = Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)) {
                if (empty($respond['success']) || empty($respond['purchaseId'])) {
                    throw new Mage_Payment_Model_Info_Exception(Mage::helper('ls_cashpresso')->__($respond['error']['description']));
                }
            }
        }

        return true;
    }
}