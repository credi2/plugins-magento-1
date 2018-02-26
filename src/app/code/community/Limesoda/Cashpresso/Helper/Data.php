<?php

/**
 * 13.02.18
 * LimeSoda - Default (Template) Project
 *
 * Created by Anton Sannikov.
 *
 * @category    Limesoda_Cashpresso
 * @package     Default (Template) Project
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file app/code/local/Limesoda/Cashpresso/Helper/Data.php
 */
class Limesoda_Cashpresso_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PARTNER_INFO = 'cashpresso/partnerinfo';

    const XML_PARTNER_API_KEY = 'payment/cashpresso/api_key';

    const XML_PARTNER_STATUS = 'payment/cashpresso/status';

    const XML_PARTNER_MODE = 'payment/cashpresso/mode';

    const XML_PARTNER_WIDGET_TYPE = 'payment/cashpresso/widget_type';

    const XML_PARTNER_TEMPLATE = 'payment/cashpresso/template';

    const XML_PARTNER_TIMEOUT = 'payment/cashpresso/timeout';

    const XML_PARTNER_SECRET_KEY = 'payment/cashpresso/secret_key';

    const XML_PARTNER_CONTRACT_TEXT = 'payment/cashpresso/sign_contract_text';

    const XML_PARTNER_PLACE_TO_SHOW = 'payment/cashpresso/place_to_show';

    const XML_PARTNER_CHECKOUT_BUTTON = 'payment/cashpresso/checkout_button';

    const XML_PARTNER_DESCRIPTION = 'payment/cashpresso/description';

    const XML_PARTNER_DEBUG_MODE = 'payment/cashpresso/debug_mode';

    const XML_PARTNER_TARGET_ACCOUNT = 'payment/cashpresso/account';

    /**
     * @return mixed
     */
    public function getPartnerInfo()
    {
        return Mage::helper('core')->jsonDecode(Mage::getStoreConfig(self::XML_PARTNER_INFO));
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return  Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PARTNER_SECRET_KEY));
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function generatePartnerInfo()
    {
        if (!$this->getAPIKey()) {
            return false;
        }

        $partnerInfo = Mage::getModel('ls_cashpresso/api_client')->setMode($this->getMode())->getPartnerInfo();
        Mage::app()->getConfig()->saveConfig(self::XML_PARTNER_INFO, Mage::helper('core')->jsonEncode($partnerInfo));

        return $partnerInfo;
    }

    /**
     * @return mixed
     */
    public function getAPIKey()
    {
        return Mage::getStoreConfig(self::XML_PARTNER_API_KEY);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getStatus($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_STATUS, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getTimeout($storeId = null)
    {
        $hrs = Mage::getStoreConfig(self::XML_PARTNER_TIMEOUT, $storeId);

        return Mage::getSingleton('core/date')->date(DATE_ATOM, strtotime("+$hrs hours"));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function getInterestFreeDay()
    {
        if (!$this->getAPIKey()) {
            return false;
        }

        $partnerInfo = $this->generatePartnerInfo();

        return isset($partnerInfo['interestFreeCashpresso']) ? $partnerInfo['interestFreeCashpresso'] : false;
    }

    /**
     * 1 - live; 0 - test
     * @param null $storeId
     * @return mixed
     */
    public function getMode($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_MODE, $storeId);
    }

    /**
     *
     * @param null $storeId
     * @return mixed
     */
    public function getWidgetType($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_WIDGET_TYPE, $storeId);
    }

    /**
     *
     * @param null $storeId
     * @return mixed
     */
    public function getTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_TEMPLATE, $storeId);
    }

    /**
     *
     * @param null $storeId
     * @return mixed
     */
    public function getContractText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_CONTRACT_TEXT, $storeId);
    }

    /**
     *
     * @param null $storeId
     * @return mixed
     */
    public function getPlaceToShow($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_PLACE_TO_SHOW, $storeId);
    }

    /**
     *
     * @param null $storeId
     * @return mixed
     */
    public function showCheckoutButton($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_CHECKOUT_BUTTON, $storeId);
    }

    /**
     * @return bool
     */
    public function checkStatus()
    {
        return !$this->isModuleEnabled() || !Mage::getModel('ls_cashpresso/payment_method_cashpresso')->getConfigData('active') || !$this->getStatus() || !$apiKey = $this->getAPIKey();
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return Mage::getStoreConfig(self::XML_PARTNER_DESCRIPTION);
    }

    /**
     * @return mixed
     */
    public function isDebugEnabled()
    {
        return Mage::getStoreConfig(self::XML_PARTNER_DEBUG_MODE);
    }

    /**
     * @return bool
     */
    public function isProductPage()
    {
        return Mage::registry('current_product')?true:false;
    }

    /**
     * @return mixed
     */
    public function getCheckoutUrl()
    {
        $urlObject = new stdClass();
        $urlObject->url = $this->_getUrl('checkout/onepage', array('_secure'=>true));

        Mage::dispatchEvent('cashpresso_js_c2checkout_url', array('url' => $urlObject));

        return $urlObject->url;
    }

    /**
     * @return mixed
     */
    public function getTargetAccount()
    {
        return Mage::getStoreConfig(self::XML_PARTNER_TARGET_ACCOUNT);
    }
}