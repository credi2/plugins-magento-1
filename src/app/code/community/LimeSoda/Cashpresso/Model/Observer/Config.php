<?php


class LimeSoda_Cashpresso_Model_Observer_Config
{
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    public function adminSystemConfigChangedSection(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');

        list($websiteId, $currentStoreId) = $this->_helper()->getCurrentStore();

        if (!$this->_helper()->getContractCurrency()) {
            $this->_helper()->generatePartnerInfo();
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
    }
}