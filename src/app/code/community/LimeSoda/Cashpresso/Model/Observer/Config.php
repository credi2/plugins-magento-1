<?php

class LimeSoda_Cashpresso_Model_Observer_Config
{
    public function adminSystemConfigChangedSection(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');

        list($websiteId, $currentStoreId) = $this->_helper()->getCurrentStore();

        if (!$this->_helper()->getContractCurrency()) {
            $info = $this->_helper()->generatePartnerInfo($showError = true);
            Mage::getConfig()->reinit();
        }

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                /** @var Mage_Core_Model_Store $store */
                foreach ($stores as $store) {
                    if ($currentStoreId == $store->getId() || !$currentStoreId || $websiteId) {
                        $currency = Mage::app()->getStore($store->getId())->getCurrentCurrencyCode();

                        if ($this->_helper()->getContractCurrency() && ($currency != $this->_helper()->getContractCurrency())) {
                            if (Mage::getStoreConfig('payment/cashpresso/active', $store->getId())) {
                                $session->addError($this->_helper()->__('Currency %s for the store "%s" could not be used for Cashpresso payment. Use only EUR. Please change the currency settings for your store.', $currency, $store->getName()));
                                Mage::getConfig()->saveConfig('payment/cashpresso/active', 0, 'stores', $store->getId());
                            } else {
                                $session->addNotice($this->_helper()->__('Currency %s for the store "%s" could not be used for Cashpresso payment. Use only EUR. Please change the currency settings for your store.', $currency, $store->getName()));
                            }
                        }
                    }
                }
            }
        }

        if (!$this->_helper()->getAPIKey()) {
            $session->addError($this->_helper()->__("Cashpresso: API key is missing"));
        }

        if (!$this->_helper()->getSecretKey()) {
            $session->addError($this->_helper()->__("Cashpresso: Secret key is missing"));
        }

        if (!$this->_helper()->getAPIKey($loadDefaultValue = false) && !$this->_helper()->getSecretKey($loadDefaultValue = false)) {
            $scope = 'default';

            if (list($scopeID) = $this->_helper()->getCurrentStore()) {
                $scope = 'websites';
            }

            Mage::app()->getConfig()->deleteConfig(LimeSoda_Cashpresso_Helper_Data::XML_PARTNER_INFO, $scope, (int)$scopeID);
        }

        if ($this->_helper()->getAPIKey() && $this->_helper()->getSecretKey() && empty($info)) {
            $this->_helper()->generatePartnerInfo($showError = true);
        }

        try {
            Mage::getModel('ls_cashpresso/api_account')->checkRequest();
        } catch (Exception $e) {
            $session->addError($e->getMessage());

            if ($websiteId) {
                Mage::getConfig()->saveConfig('payment/cashpresso/active', 0, 'websites', $websiteId);
            } else if ($currentStoreId) {
                Mage::getConfig()->saveConfig('payment/cashpresso/active', 0, 'stores', $currentStoreId);
            } else {
                Mage::getConfig()->saveConfig('payment/cashpresso/active', 0, 'default', $currentStoreId);
            }
        }
    }

    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }
}