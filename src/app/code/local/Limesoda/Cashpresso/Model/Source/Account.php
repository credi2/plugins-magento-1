<?php
/**
 * 19.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Account.php
 */

class Limesoda_Cashpresso_Model_Source_Account
{

    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Live')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Test')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('Test'),
            1 => Mage::helper('adminhtml')->__('Live'),
        );
    }
}