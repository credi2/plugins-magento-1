<?php

/**
 * 13.02.18
 * LimeSoda - Default (Template) Project
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     Default (Template) Project
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file app/code/local/LimeSoda/Cashpresso/Helper/Data.php
 */
class LimeSoda_Cashpresso_Helper_Data extends Mage_Core_Helper_Abstract
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

    const XML_PARTNER_DEBUG_MODE = 'payment/cashpresso/debug_mode';

    const XML_PARTNER_TARGET_ACCOUNT = 'payment/cashpresso/account';

    const XML_PARTNER_CHECKOUT_URL = 'payment/cashpresso/checkout_url';

    const XML_CASHPRESSO_PRODUCT_TYPES = 'frontend/cashpresso/product_types';

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
        return Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PARTNER_SECRET_KEY));
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

        $partnerInfo['last_update'] = Mage::getSingleton('core/date')->date();

        Mage::app()->getConfig()->saveConfig(self::XML_PARTNER_INFO, Mage::helper('core')->jsonEncode($partnerInfo));

        return $partnerInfo;
    }

    /**
     * @return mixed
     */
    public function getAPIKey()
    {
        return trim(Mage::getStoreConfig(self::XML_PARTNER_API_KEY));
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

        $dateHelper = Mage::getSingleton('core/date');

        return $this->getConvertTime($dateHelper->timestamp(), $hrs);
    }

    public function getConvertTime($date, $hrs)
    {
        $date = new Zend_Date($date);
        $date->addHour($hrs);
        return date(DATE_ATOM, $date->getTimestamp());
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
     *
     * @param null $storeId
     * @return mixed
     */
    public function getCheckoutUrl($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_CHECKOUT_URL, $storeId);
    }

    /**
     * @param bool $useStatus
     * @return bool
     */
    public function checkStatus($useStatus = true)
    {
        return $this->isModuleEnabled() && Mage::getModel('ls_cashpresso/payment_method_cashpresso')->getConfigData('active') && ($useStatus?$this->getStatus():true) && ($apiKey = $this->getAPIKey());
    }

    /**
     * @return mixed
     */
    public function isDebugEnabled()
    {
        return Mage::getStoreConfig(self::XML_PARTNER_DEBUG_MODE);
    }

    /**
     * @return mixed
     */
    public function getTargetAccount()
    {
        return Mage::getStoreConfig(self::XML_PARTNER_TARGET_ACCOUNT);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getProductTypes($storeId = null)
    {
        return Mage::app()->getConfig()->getNode(self::XML_CASHPRESSO_PRODUCT_TYPES)->asArray();
    }
}