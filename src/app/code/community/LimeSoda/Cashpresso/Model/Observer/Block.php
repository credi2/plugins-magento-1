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
     * @throws Mage_Core_Exception
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
        $accounts = Mage::getModel('ls_cashpresso/api_account')->getTargetAccounts();

        if ($this->_helper()->checkStatus()) {
            return;
        }

        $block = $observer->getEvent()->getBlock();

        $transport = $observer->getEvent()->getTransport();

        if ($block instanceof Mage_Checkout_Block_Onepage_Success) {
            $this->addScriptSuccessPage($transport);
        } else if ($block instanceof Mage_Catalog_Block_Product_Price) {
            if (($this->_helper()->getPlaceToShow() == 1 && !$this->_helper()->isProductPage()) ||
                ($this->_helper()->getPlaceToShow() == 2 && $this->_helper()->isProductPage()) ||
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

    /**
     * @param $transport
     * @param $block
     * @throws Mage_Core_Exception
     */
    public function addScriptToPrice($transport, $block)
    {
        $product = $block->getProduct();

        if ($block->hasData('in_grouped') || Mage::registry('ls_cs_addScriptToPrice')) {
            return;
        }

        if ($this->_helper()->isProductPage()) {
            Mage::register('ls_cs_addScriptToPrice', true);
        }

        $html = Mage::app()->getLayout()->createBlock('ls_cashpresso/button')->setProduct($product)->emulateToHtml();

        $transport->setHtml($transport->getHtml() . $html);
    }
}