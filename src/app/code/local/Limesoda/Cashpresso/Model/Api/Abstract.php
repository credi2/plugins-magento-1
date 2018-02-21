<?php
/**
 * 19.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Abstract.php
 */

abstract class Limesoda_Cashpresso_Model_Api_Abstract
{
    protected $_mode = false;

    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    public function getPartnerApiKey()
    {
        return Mage::helper('ls_cashpresso')->getAPIKey();
    }

    public function getSecretKey()
    {
        return Mage::helper('ls_cashpresso')->getSecretKey();
    }

    public function setMode($test = false)
    {
        $this->_mode = $test;

        return $this;
    }

    protected function getTestUrl()
    {
        return "https://test.cashpresso.com/rest/backend/ecommerce/v2/";
    }

    protected function getLiveUrl()
    {
        return "https://backend.cashpresso.com/rest/backend/ecommerce/v2/";
    }

    protected function getRequest($method)
    {
        $client = new Varien_Http_Client(($this->_mode ? $this->getLiveUrl() : $this->getTestUrl()) . $method);
        $client->setMethod(Varien_Http_Client::POST);
        $client->setRawData(json_encode([
                'partnerApiKey' => $this->getPartnerApiKey()]
        ), 'application/json');

        return $client;
    }
}