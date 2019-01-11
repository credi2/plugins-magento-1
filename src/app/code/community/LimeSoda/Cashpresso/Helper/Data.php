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

    const XML_CASHPRESSO_INTEREST_FREE_DAYS_MERCHANT = 'payment/cashpresso/interestFreeDaysMerchant';

    protected $_partnerInfo;

    /**
     * @param bool $loadDefaultValue
     * @return null|string
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSecretKey($loadDefaultValue = true)
    {
        list($scopeID) = $this->getCurrentStore();

        if (Mage::app()->getStore()->isAdmin()) {
            $collection = Mage::getResourceModel('core/config_data_collection');

            $collection->getSelect()
                ->where('path = ?', self::XML_PARTNER_SECRET_KEY)
                ->where('scope_id = ?', (int)$scopeID);

            $config = $collection->load()->getFirstItem();

            if (!$config->getId() && $loadDefaultValue) {
                $collection->getSelect()->reset(Varien_Db_Select::WHERE)
                    ->where('path = ?', self::XML_PARTNER_SECRET_KEY)
                    ->where('scope = ?', 'default')
                    ->where('scope_id = ?', 0);

                $config = $collection->clear()->load()->getFirstItem();
            }

            $key = $config->getId() ? $config->getValue() : null;
        } else {
            $key = Mage::getStoreConfig(self::XML_PARTNER_SECRET_KEY);
        }

        return $key ? Mage::helper('core')->decrypt($key) : null;
    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getCurrentStore()
    {
        $website_id = 0;
        $store_id = 0;

        if (Mage::app()->getStore()->isAdmin()) {
            if (($code = Mage::getSingleton('adminhtml/config_data')->getStore()) !== "") {// store level
                $store_id = Mage::getModel('core/store')->load($code)->getId();
            } elseif (($code = Mage::getSingleton('adminhtml/config_data')->getWebsite()) !== "") { // website level
                $website_id = Mage::getModel('core/website')->load($code)->getId();
                $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
            }
        } else {
            $website_id = Mage::app()->getWebsite()->getId();
            $store_id = Mage::app()->getStore()->getId();
        }

        return array($website_id, $store_id);
    }

    /**
     * @param bool $showError
     * @return bool
     */
    public function generatePartnerInfo($showError = false)
    {
        if (!$this->getAPIKey()) {
            return false;
        }

        if ($this->_partnerInfo === null) {
            $partnerInfo = Mage::getModel('ls_cashpresso/api_client')->getPartnerInfo($showError);

            $partnerInfo['last_update'] = Mage::getSingleton('core/date')->date();

            $scope = 'default';

            if (list($scopeID) = $this->getCurrentStore()) {
                if ($scopeID != 0) {
                    $scope = 'websites';
                }
            }

            if ($currentStoreKey = $this->getAPIKey($loadDefaultValue = false)) {
                Mage::app()->getConfig()->saveConfig(self::XML_PARTNER_INFO, Mage::helper('core')->jsonEncode($partnerInfo), $scope, (int)$scopeID);
            }

            $this->_partnerInfo = $partnerInfo;
        }

        return $this->_partnerInfo;
    }

    /**
     * @param bool $loadDefaultValue
     * @return null|string
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getAPIKey($loadDefaultValue = true)
    {
        if (Mage::app()->getStore()->isAdmin()) {
            list($scopeID) = $this->getCurrentStore();
            $collection = Mage::getResourceModel('core/config_data_collection');

            $collection->getSelect()
                ->where('path = ?', self::XML_PARTNER_API_KEY)
                ->where('scope_id = ?', (int)$scopeID);

            $config = $collection->load()->getFirstItem();

            if (!$config->getId() && $loadDefaultValue) {
                $collection->getSelect()->reset(Varien_Db_Select::WHERE)
                    ->where('path = ?', self::XML_PARTNER_API_KEY)
                    ->where('scope = ?', 'default')
                    ->where('scope_id = ?', 0);

                $config = $collection->clear()->load()->getFirstItem();
            }

            return $config->getId() ? $config->getValue() : null;
        }

        return trim(Mage::getStoreConfig(self::XML_PARTNER_API_KEY));
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

        $customValue = Mage::getStoreConfig(self::XML_CASHPRESSO_INTEREST_FREE_DAYS_MERCHANT);

        $cashpressoValue = empty($partnerInfo['interestFreeMaxDuration']) ? 0 : $partnerInfo['interestFreeMaxDuration'];

        return $cashpressoValue && ($customValue > $cashpressoValue) ? $cashpressoValue : $customValue;
    }

    /**
     * @return array|mixed
     * @throws Mage_Core_Exception
     */
    public function getPartnerInfo()
    {
        $scopeID = null;
        
        if (Mage::app()->getStore()->isAdmin()) {
            list($scopeID) = $this->getCurrentStore();
        }

        $partnerInfo = Mage::getStoreConfig(self::XML_PARTNER_INFO, $scopeID);

        return $partnerInfo ? Mage::helper('core')->jsonDecode($partnerInfo) : array();
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
        return $this->isModuleEnabled() && Mage::getModel('ls_cashpresso/payment_method_cashpresso')->getConfigData('active') && ($useStatus ? $this->getStatus() : true) && ($apiKey = $this->getAPIKey());
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
    public function getMinLimit()
    {
        $partnerInfo = $this->getPartnerInfo();

        return empty($partnerInfo['minPaybackAmount']) ? 0 : $partnerInfo['minPaybackAmount'];
    }

    /**
     * @return null
     */
    public function getContractCurrency()
    {
        $partnerInfo = $this->getPartnerInfo();

        return empty($partnerInfo['currency']) ? null : $partnerInfo['currency'];
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
     *
     * @param null $storeId
     * @return mixed
     */
    public function getWidgetType($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PARTNER_WIDGET_TYPE, $storeId);
    }

    protected function _getDomain()
    {
        return 'https://' . ($this->getMode() ? 'my.cashpresso.com' : 'test.cashpresso.com/frontend') . '/';
    }

    /**
     * 1 - live; 0 - test
     * @param null $storeId
     * @return mixed
     */
    public function getMode($storeId = null)
    {
        if (Mage::app()->getStore()->isAdmin()) {

            list($storeId) = $this->getCurrentStore();

            $collection = Mage::getResourceModel('core/config_data_collection');

            $collection->getSelect()
                ->where('path = ?', self::XML_PARTNER_MODE)
                ->where('scope_id = ?', (int)$storeId);

            $config = $collection->load()->getFirstItem();

            return $config->getValue();
        }

        return Mage::getStoreConfig(self::XML_PARTNER_MODE, $storeId);
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
                $price * 0.01 * $partnerInfo['paybackRate'])), 2);
        }

        return $minPayment;
    }
}