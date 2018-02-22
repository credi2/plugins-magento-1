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
 * @file Cashondelivery.php
 */


/**
 * Cash on delivery payment method model
 */
class Limesoda_Cashpresso_Model_Payment_Method_Cashpresso extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code  = 'cashpresso';

    /**
     * Check whether method is available
     *
     * @param null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        //&& Mage::app()->getStore()->roundPrice($quote->getGrandTotal()) == 0;
        return parent::isAvailable($quote) && !empty($quote) && Mage::helper('ls_cashpresso')->isModuleEnabled();
    }

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'ls_cashpresso/payment_form_cashpresso';
    //protected $_infoBlockType = 'ls_cashpresso/payment_info';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

}
