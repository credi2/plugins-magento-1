<?php
/**
 * 15.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    Limesoda_Cashpresso
 * @package     cashpresso
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