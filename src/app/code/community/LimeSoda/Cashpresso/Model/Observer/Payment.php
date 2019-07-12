<?php


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
                throw new Mage_Payment_Exception($this->_helper()->__('Unable to set Payment Method.'));
            }

            if ($paymentMethod->getQuote()->getGrandTotal() <= $this->_helper()->getMinLimit()) {
                throw new Mage_Payment_Exception($this->_helper()->__('Unable to set Payment Method.'));
            }

            if (!$token = Mage::app()->getRequest()->getPost('cashpressoToken')) {
                throw new Mage_Payment_Exception($this->_helper()->__('Please login or register to continue with cashpresso.'));
            }

            $additionalData = json_decode($paymentMethod->getAdditionalData, true);
            $additionalData['cashpresso']['token'] = $token;
            $paymentMethod->setAdditionalData(json_encode($additionalData));
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

        if ($payment->getOrder()->getPayment()->getMethod() == 'cashpresso' && $hash = $payment->getOrder()->getPayment()->getAdditionalData()) {
            $purchaseId = Mage::getModel('ls_cashpresso/api_client')->sendOrder($payment->getOrder());
            $additionalData = json_decode($payment->getOrder()->getPayment()->getAdditionalData(), true);
            $additionalData['cashpresso']['purchase_id'] = $purchaseId;
            $payment->getOrder()->getPayment()->setAdditionalData(json_encode($additionalData));
        }
    }

    /**
     * @event controller_front_send_response_before
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Model_Store_Exception
     */
    public function updatePrice(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Controller_Varien_Front $front */
        $front = $observer->getEvent()->getFront();

        $actions = new Varien_Object();
        $actions->setData('list', array('saveShippingMethod'));

        Mage::dispatchEvent('cs_payment_block_result_before', array(
            'actions' => $actions
        ));

        if (in_array($front->getRequest()->getActionName(), $actions->getData('list'))) {
            $quote = Mage::getModel("checkout/session")->getQuote();
            $amount = Mage::app()->getStore()->roundPrice($quote->getGrandTotal());

            $response = $front->getResponse()->getBody();

            $result = Mage::helper('core')->jsonDecode($response);

            if (!empty($result['update_section']['html'])) {
                $html = $result['update_section']['html'];

                $html .= <<<EOT
<script type="text/javascript">
    //<![CDATA[
    if (window.C2EcomCheckout) {
        window.C2EcomCheckout.refresh();
        $('c2CheckoutScript').writeAttribute('data-c2-amount', "{$amount}");
        window.C2EcomCheckout.init();
    }
    //]]>
</script>
EOT;
                $result['update_section']['html'] = $html;

                Mage::dispatchEvent('cs_payment_block_result_after', array(
                    'actions' => $actions
                ));

                $front->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    }
}