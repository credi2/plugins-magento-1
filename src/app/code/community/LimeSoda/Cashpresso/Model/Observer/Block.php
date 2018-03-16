<?php
/**
 * 14.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Block.php
 */

class LimeSoda_Cashpresso_Model_Observer_Block
{
    /**
     * @return LimeSoda_Cashpresso_Helper_Data|Mage_Core_Helper_Abstract
     */
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function setOrder(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $orderId = current($orderIds);
        Mage::register('ls_success_order', Mage::getModel('sales/order')->load($orderId));
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        if (!$this->_helper()->checkStatus()) {
            return;
        }

        $block = $observer->getEvent()->getBlock();

        $transport = $observer->getEvent()->getTransport();

        if ($block instanceof Mage_Checkout_Block_Onepage_Success) {
            $this->addScriptSuccessPage($transport);
        } else if ($block instanceof Mage_Catalog_Block_Product_Price) {
            if (($this->_helper()->getPlaceToShow() == 1 && !Mage::helper('ls_cashpresso/request')->isProductPage()) ||
                ($this->_helper()->getPlaceToShow() == 2 && Mage::helper('ls_cashpresso/request')->isProductPage()) ||
                ($this->_helper()->getPlaceToShow() == 3)
            ) {
                $this->addScriptToPrice($transport, $block);
            }
        }
    }

    /**
     * @param $transport
     * @throws Exception
     */
    public function addScriptSuccessPage($transport)
    {
        $html = $transport->getHtml();
        $mode = $this->_helper()->getMode() ? 'live' : 'test';
        $apiKey = $this->_helper()->getAPIKey();
        list($locale) = explode('_', strtolower(Mage::app()->getLocale()->getLocaleCode()));

        if ($apiKey && Mage::registry('ls_success_order')) {

            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::registry('ls_success_order');

            $purchaseId = $order->getPayment()->getAdditionalData();

            $successMessage = $this->_helper()->getSuccessText();

            $successTitle = Mage::helper('checkout')->__('Your order has been received.');

            $script = <<<EOT
<script type="text/javascript">
//<![CDATA[
document.addEventListener("DOMContentLoaded", function (event) {"use strict";function c2SuccessCallback() {var successTitle = document.getElementById('ls_cashpresso_success_title');if (typeof(successTitle) != "undefined"){successTitle.innerHTML = "{$successTitle}";}var contractSpan = document.getElementById('ls_cashpresso_contract_text');if (typeof(contractSpan) != "undefined"){contractSpan.innerHTML = "{$successMessage}";}}});
//]]>
</script> 
<script id="c2PostCheckoutScript" type="text/javascript"
    src="https://my.cashpresso.com/ecommerce/v2/checkout/c2_ecom_post_checkout.all.min.js"
    defer
    data-c2-partnerApiKey="{$apiKey}"
    data-c2-purchaseId="{$purchaseId}"
    data-c2-mode="{$mode}"
    data-c2-locale="{$locale}">
    data-c2-successCallback="true"
</script>
EOT;
            $helper = Mage::helper('cms');
            $processor = $helper->getPageTemplateProcessor();
            $message = $processor->filter($this->_helper()->getContractText());

            $transport->setHtml($html . $script . "<p class=\"a-center\" style=\"margin-top:10px;\" id=\"ls_cashpresso_contract_text\">{$message}</p>");
        }
    }

    /**
     * @param $transport
     * @param $block Mage_Catalog_Block_Product_Price
     */
    public function addScriptToPrice($transport, $block)
    {
        $product = $block->getProduct();

        $hash = md5('ls_cs_addScriptToPrice_' . $product->getId());

        if ($block->hasData('in_grouped') || Mage::registry($hash)) {
            return;
        }

        if (Mage::helper('ls_cashpresso/request')->isProductPage()) {
            Mage::register($hash, true);
        }

        $html = Mage::app()->getLayout()->createBlock('ls_cashpresso/button')->setProduct($product)->emulateToHtml();

        $transport->setHtml($transport->getHtml() . $html);
    }
}