<?php

/**
 * 13.02.18
 * LimeSoda - Default (Template) Project
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     Default (Template) Project
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file app/code/local/Limesoda/Cashpresso/Helper/Data.php
 */
class Limesoda_Cashpresso_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PARTNER_INFO = 'cashpresso/partnerinfo';

    const XML_PARTNER_API_KEY = 'cashpresso/options/api_key';

    const XML_PARTNER_STATUS = 'cashpresso/options/status';

    const XML_PARTNER_MODE = 'cashpresso/options/mode';

    const XML_PARTNER_WIDGET_TYPE = 'cashpresso/options/widget_type';

    /**
     * @return mixed
     */
    public function getPartnerInfo()
    {
        return Mage::helper('core')->jsonDecode(Mage::getStoreConfig(self::XML_PARTNER_INFO));
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

        $partnerInfo = Mage::getModel('ls_cashpresso/api')->setMode($this->getMode())->getPartnerInfo();
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
     * 1 - live; 0 - test
     * @param null $storeId
     * @return mixed
     */
    public function getMode($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_MODE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getWidgetType($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_WIDGET_TYPE, $storeId);
    }
}