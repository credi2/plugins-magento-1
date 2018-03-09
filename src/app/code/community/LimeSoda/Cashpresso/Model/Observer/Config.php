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
        $session = Mage::getSingleton('adminhtml/session');

        if (!Mage::helper('ls_cashpresso')->getAPIKey()) {
            $session->addError(Mage::helper('ls_cashpresso')->__('API key is empty'));
        }

        if (!Mage::helper('ls_cashpresso')->getSecretKey()) {
            $session->addError(Mage::helper('ls_cashpresso')->__('Secret key is empty'));
        }
    }
}