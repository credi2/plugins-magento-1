<?php


class LimeSoda_Cashpresso_Model_Adminhtml_System_Config_Source_Widget
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Product level integration')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Static Label integration')),
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
            0 => Mage::helper('adminhtml')->__('Static Label integration'),
            1 => Mage::helper('adminhtml')->__('Product level integration'),
        );
    }
}