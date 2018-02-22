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
 * @file Widget.php
 */

class Limesoda_Cashpresso_Model_Adminhtml_System_Config_Source_Places
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Product')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Catalog')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Catalog and Product')),
        );
    }

    /**⁄
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('Catalog and Product'),
            1 => Mage::helper('adminhtml')->__('Catalog'),
            2 => Mage::helper('adminhtml')->__('Product'),
        );
    }
}