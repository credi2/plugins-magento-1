<?php
/**
 * 20.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Payment.php
 */

class LimeSoda_Cashpresso_Model_Observer_Payment
{
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function addPostHashOrder(Varien_Event_Observer $observer)
    {
        /** @var Varien_Object $data */
        $data = $observer->getEvent()->getInput();

        /** @var Mage_Sales_Model_Quote_Payment $paymentMethod */
        $paymentMethod = $observer->getEvent()->getPayment();

        if ($data->getMethod() == Mage::getModel('ls_cashpresso/payment_method_cashpresso')->getCode()) {
            if ($paymentMethod->getQuote()->getGrandTotal() >= $this->_helper()->getTotalLimit()) {
                throw new Exception($this->_helper()->__('Unable to set Payment Method.'));
            }
        }

        if ($token = Mage::app()->getRequest()->getPost('cashpressoToken')) {
            $paymentMethod->setAdditionalData($token);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function sendOrder(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getEvent()->getPayment();

        if ($hash = $payment->getOrder()->getPayment()->getAdditionalData()) {
            $purchaseId = Mage::getModel('ls_cashpresso/api_client')->sendOrder($payment->getOrder());
            $payment->getOrder()->getPayment()->setAdditionalData($purchaseId);
        }
    }
}