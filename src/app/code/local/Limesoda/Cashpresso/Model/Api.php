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

class Limesoda_Cashpresso_Model_Api
{
    const METHOD_PARTNER_INFO = 'partnerInfo';
    const METHOD_BUY = 'buy';
    const METHOD_SIMULATION = 'simulation/callback';

    protected $_testMode = false;

    protected $_post = array();

    public function getPartnerApiKey()
    {
        return Mage::helper('ls_cashpresso')->getAPIKey();
    }

    public function setMode($test = false)
    {
        $this->_testMode = $test;

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
        $client = new Varien_Http_Client(($this->_testMode ? $this->getTestUrl() : $this->getLiveUrl()) . $method);
        $client->setMethod(Varien_Http_Client::POST);
        $client->setRawData(json_encode([
                'partnerApiKey' => $this->getPartnerApiKey()]
        ), 'application/json');

        return $client;
    }

    public function getPartnerInfo()
    {
        $request = $this->getRequest(self::METHOD_PARTNER_INFO);

        $response = $request->request();

        if ($response->isSuccessful()) {
            $responde =  Mage::helper('core')->jsonDecode($response->getBody());

            if (is_array($responde)){
                if (empty($responde['success'])){
                    throw new Exception(Mage::helper('ls_cashpresso')->__($responde['error']['description']));
                }

                return $responde;
            }
        }

        throw new Exception(Mage::helper('ls_cashpresso')->__($response->getMessage()));
    }

    public function sendSimulationCallbackRequest()
    {
        $request = $this->getRequest(self::METHOD_SIMULATION);
    }

    public function sendBuyRequest()
    {
        $request = $this->getRequest(self::METHOD_BUY);
    }

    public function send()
    {
        $client = new Varien_Http_Client('https://test.cashpresso.com/rest/backend/ecommerce/v2/partnerInfo');

        $client->setMethod(Varien_Http_Client::POST);
        $client->setRawData(json_encode([
                'partnerApiKey' => '908db109be694529dd0c1331afe4e5ae74b41176c3d64dea99f798ff8f7cc7ab622de47a92d52a560a971e7d0ce68b0ea0d1bab243fc859bf80f76e93987fbe2']
        ), 'application/json');

        //more parameters
        try {
            $response = $client->request();
            if ($response->isSuccessful()) {
                echo '<pre><br/>';
                var_dump(Mage::helper('core')->jsonDecode($response->getBody()));
                die();
            }
        } catch (Exception $e) {
            echo '<pre><br/>';
            var_dump($e->getMessage());
            die();
        }
        die();
    }
}