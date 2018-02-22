<?php
/**
 * 22.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Button.php
 */

class Limesoda_Cashpresso_Helper_Button extends Mage_Core_Helper_Abstract
{
    /**
     * @return Limesoda_Cashpresso_Helper_Data|Mage_Core_Helper_Abstract
     */
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    /**
     * @return bool
     */
    public function isProductPage()
    {
        return Mage::registry('current_product')?true:false;
    }

    /**
     * @return mixed
     */
    public function getCheckoutUrl()
    {
        $urlObject = new stdClass();
        $urlObject->url = $this->_getUrl('checkout/onepage', array('_secure'=>true));

        Mage::dispatchEvent('cashpresso_js_c2checkout_url', array('url' => $urlObject));

        return $urlObject->url;
    }
}