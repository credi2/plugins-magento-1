<?php
/**
 * 19.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Cashondelivery.php
 */


/**
 * Cash on delivery payment method model
 */
class LimeSoda_Cashpresso_Model_Payment_Method_Cashpresso extends Mage_Payment_Model_Method_Abstract
{
    protected $_canUseInternal = false;
    protected $_isInitializeNeeded = true;
    protected $_canCancelInvoice = true;

    public function canEdit()
    {
        return false;
    }

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code  = 'cashpresso';

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'ls_cashpresso/payment_form_cashpresso';

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    /**
     * Check whether method is available
     *
     * @param null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return parent::isAvailable($quote) && !empty($quote) && Mage::helper('ls_cashpresso')->isModuleEnabled();
    }

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