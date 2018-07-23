<?php


class LimeSoda_Cashpresso_Model_Adminhtml_System_Config_Source_Places
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 3, 'label' => Mage::helper('adminhtml')->__('Catalog/Search and Product')),
            array('value' => 2, 'label' => Mage::helper('adminhtml')->__('Product')),
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Catalog/Search'))
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
            1 => Mage::helper('adminhtml')->__('Catalog/Search'),
            2 => Mage::helper('adminhtml')->__('Product'),
            3 => Mage::helper('adminhtml')->__('Catalog/Search and Product'),
        );
    }
}