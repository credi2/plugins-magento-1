<?php
/**
 * 15.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Cron.php
 */

class Limesoda_Cashpresso_Model_Cron
{
    public function syncSettings()
    {
        try {
            Mage::helper('ls_cashpresso')->generatePartnerInfo();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}