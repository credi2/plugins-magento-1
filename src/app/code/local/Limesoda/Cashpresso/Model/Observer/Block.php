<?php
/**
 * 14.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Block.php
 */

class Limesoda_Cashpresso_Model_Observer_Block
{
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    public function setOrder(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $orderId = current($orderIds);
        Mage::register('ls_success_order', Mage::getModel('sales/order')->load($orderId));
    }

    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Checkout_Block_Onepage_Success) {
            $transport = $observer->getEvent()->getTransport();
            $html = $transport->getHtml();

            $mode = $this->_helper()->getMode() ? 'live' : 'test';
            $apiKey = $this->_helper()->getAPIKey();
            list($locale) = explode('_', strtolower(Mage::app()->getLocale()->getLocaleCode()));

            if ($apiKey && Mage::registry('ls_success_order')){

                /** @var Mage_Sales_Model_Order $order */
                $order = Mage::registry('ls_success_order');
                
                $purchaseId = $order->getPayment()->getAdditionalData();
                
                $script = <<<EOT
<script id="c2PostCheckoutScript" type="text/javascript"
    src="https://my.cashpresso.com/ecommerce/v2/checkout/c2_ecom_post_checkout.all.min.js"
    defer
    data-c2-partnerApiKey="{$apiKey}"
    data-c2-purchaseId="{$purchaseId}"
    data-c2-mode="{$mode}"
    data-c2-locale="{$locale}">
</script>
EOT;
                $helper = Mage::helper('cms');
                $processor = $helper->getPageTemplateProcessor();
                $message = $processor->filter($this->_helper()->getContractText());



                $transport->setHtml($html . $script . $message);
            }
        }
    }
}