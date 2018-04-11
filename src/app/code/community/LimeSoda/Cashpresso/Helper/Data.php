<?php


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

    const XML_PARTNER_SUCCESS_TEXT = 'payment/cashpresso/success_text';

    const XML_PARTNER_SUCCESS_BUTTON_TITLE = 'payment/cashpresso/success_button_title';

    const XML_PARTNER_SUCCESS_TITLE = 'payment/cashpresso/success_title';

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
        $partnerInfo = Mage::getStoreConfig(self::XML_PARTNER_INFO);

        return $partnerInfo ? Mage::helper('core')->jsonDecode($partnerInfo) : array();
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        $key = Mage::getStoreConfig(self::XML_PARTNER_SECRET_KEY);

        return $key ? Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PARTNER_SECRET_KEY)) : null;
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

        $partnerInfo = $this->getPartnerInfo();

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
    public function getSuccessText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_SUCCESS_TEXT, $storeId);
    }

    /**
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSuccessButtonTitle($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_SUCCESS_BUTTON_TITLE, $storeId);
    }

    /**
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSuccessTitle($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_SUCCESS_TITLE, $storeId);
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

    /**
     * @return null
     */
    public function getTotalLimit()
    {
        $partnerInfo = $this->getPartnerInfo();

        return empty($partnerInfo['limit']['total']) ? null : $partnerInfo['limit']['total'];
    }

    /**
     * @return null
     */
    public function getContractCurrency()
    {
        $partnerInfo = $this->getPartnerInfo();

        return empty($partnerInfo['currency']) ? null : $partnerInfo['currency'];
    }


    protected function _getDomain()
    {
        return 'https://' . ($this->getMode() ? 'my.cashpresso.com' : 'test.cashpresso.com/frontend') . '/';
    }
    /**
     * @return string
     */
    public function getJsLabelScript()
    {
        $scriptStatic = !$this->getWidgetType() ? '_static' : '';

        $jsSrc = $this->_getDomain() . 'ecommerce/v2/label/c2_ecom_wizard' . $scriptStatic . '.all.min.js';

        return $jsSrc;
    }

    /**
     * @return string
     */
    public function getJsCheckoutScript()
    {
        $jsSrc = $this->_getDomain() . 'ecommerce/v2/checkout/c2_ecom_checkout.all.min.js';

        return $jsSrc;
    }

    /**
     * @return string
     */
    public function getJsPostCheckoutScript()
    {
        $jsSrc = $this->_getDomain() . 'ecommerce/v2/checkout/c2_ecom_post_checkout.all.min.js';

        return $jsSrc;
    }

    /**
     * @param $price
     * @param null $params
     * @return float|int
     */
    public function getDebt($price, $params = null)
    {
        $partnerInfo = $params ? $params : $this->getPartnerInfo();

        $minPayment = 0;

        if (isset($partnerInfo['minPaybackAmount']) && isset($partnerInfo['paybackRate'])) {
            $minPayment = round(min($price, max($partnerInfo['minPaybackAmount'],
                $price * 0.01 * $partnerInfo['paybackRate'])),2);
        }

        return $minPayment;
    }

    /**
     * @return int|mixed
     */
    public function getCurrentStore()
    {
        $website_id = null;

        if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) // store level
        {
            $store_id = Mage::getModel('core/store')->load($code)->getId();
        } elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) // website level
        {
            $website_id = Mage::getModel('core/website')->load($code)->getId();
            $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
        } else // default level
        {
            $store_id = 0;
        }

        return array($website_id, $store_id);
    }
}