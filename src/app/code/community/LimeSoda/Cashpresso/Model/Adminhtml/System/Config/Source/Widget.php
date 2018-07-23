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
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Dynamic product label')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Static product label')),
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
            0 => Mage::helper('adminhtml')->__('Static product label'),
            1 => Mage::helper('adminhtml')->__('Dynamic product label'),
        );
    }
}