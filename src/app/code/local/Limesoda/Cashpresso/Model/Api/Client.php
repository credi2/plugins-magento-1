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
            $respond =  Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)){
                if (empty($respond['success'])){
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
            'description' => 'TEST PAYMENT',
            'language' => $locale
        );

        Mage::log(print_r($data, true), Zend_Log::DEBUG, 'debug.log');

        $request->setMethod(Varien_Http_Client::POST);
        $request->setRawData(json_encode($data), 'application/json');

        $response = $request->request();
        Mage::log($response->getBody(), Zend_Log::DEBUG, 'debug.log');

        if ($response->isSuccessful()) {
            $respond =  Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($respond)){
                if (empty($respond['success']) || empty($respond['purchaseId'])){
                    throw new Mage_Payment_Model_Info_Exception(Mage::helper('ls_cashpresso')->__($respond['error']['description']));
                }

                return $respond['purchaseId'];
            }
        }

        /* {
        "callbackUrl" : String (optional),
        "invoiceAddress" : Address (optional),
        "deliveryAddress" : Address (optional),
        "merchantCustomerId" : String (optional),
        "basket" : [ BasketItem ] (optional)
      }
      The Address element:
      {
        "country" : String (2 letter iso code e.g. "DE"),
        "zip" : String,
        "city" : String,
        "street" : String,
        "housenumber" : String,
      }
      The BaketItem element:
      {
        "description" : String,
        "amount" : number,
        "times" : integer
      }*/
    }

    /**
     * @param $amount
     * @param $bankUsage
     * @param string $targetAccountId
     * @return string
     */
    public function getHash($amount, $bankUsage, $targetAccountId = '')
    {
        return Mage::helper('core')->decrypt($this->getSecretKey()) . ';' . ($amount * 100) . ';' . $this->_helper()->getInterestFreeDay() . ';' . $bankUsage . ';' . $targetAccountId;
    }

    public function sendSimulationCallbackRequest()
    {
        $request = $this->getRequest(self::METHOD_SIMULATION);
    }
}