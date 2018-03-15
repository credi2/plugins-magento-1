<?php
/**
 * 08.03.18
 * LimeSoda - dockerized-magento (Cashpresso)
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento (Cashpresso)
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Config.php
 */

class LimeSoda_Cashpresso_Model_Observer_Config
{
    public function adminSystemConfigChangedSection(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');

        //list($websiteId, $currentStoreId) = $this->getCurrentStore();

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                /** @var Mage_Core_Model_Store $store */
                foreach ($stores as $store) {
                    $currency = Mage::app()->getStore($store->getId())->getCurrentCurrencyCode();

                    if ($currency != "EUR") {
                        if (Mage::getStoreConfig('payment/cashpresso/active', $store->getId())) {
                            $session->addError(Mage::helper('ls_cashpresso')->__('Currency %s for the store "%s" could not be used for Cashpresso payment. Use only EUR. Please change the currency settings of you store.', $currency, $store->getName()));
                            Mage::getConfig()->saveConfig('payment/cashpresso/active', 0, 'stores', $store->getId());
                        } else {
                            $session->addNotice(Mage::helper('ls_cashpresso')->__('Currency %s for the store "%s" could not be used for Cashpresso payment. Use only EUR. Please change the currency settings of you store.', $currency, $store->getName()));
                        }
                    }
                }
            }
        }

        //$currency = Mage::app()->getStore($currentStoreId)->getCurrentCurrencyCode();

        if (!Mage::helper('ls_cashpresso')->getAPIKey()) {
            $session->addError(Mage::helper('ls_cashpresso')->__("Cashpresso: API key is empty"));
        }

        if (!Mage::helper('ls_cashpresso')->getSecretKey()) {
            $session->addError(Mage::helper('ls_cashpresso')->__("Cashpresso: Secret key is empty"));
        }
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