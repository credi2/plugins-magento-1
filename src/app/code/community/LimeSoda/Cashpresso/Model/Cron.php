<?php


class LimeSoda_Cashpresso_Model_Cron
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