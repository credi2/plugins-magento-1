<?php


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

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);

        Mage::register('ls_success_order', $order);
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        $transport = $observer->getEvent()->getTransport();

        if ($block instanceof Mage_Checkout_Block_Onepage_Success && $this->_helper()->checkStatus(false)) {
            $this->addScriptSuccessPage($transport);
        } else if ($block instanceof Mage_Catalog_Block_Product_Price && $this->_helper()->checkStatus()) {
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

            $additionalData = json_decode($order->getPayment()->getAdditionalData(), true);
            $purchaseId = isset($additionalData['cashpresso']['purchase_id']) ? $additionalData['cashpresso']['purchase_id'] : null;

            $successMessage = $this->_helper()->getSuccessText();

            $successButtonTitle = $this->_helper()->getSuccessButtonTitle();

            $successTitle = $this->_helper()->getSuccessTitle();

            $jsScr = $this->_helper()->getJsPostCheckoutScript();

            $script = <<<EOT
<script type="text/javascript">
//<![CDATA[
if (!window.c2SuccessCallback) { window.c2SuccessCallback = function() {
var successTitle = document.getElementById('ls_cashpresso_success_title');
if (typeof(successTitle) != "undefined"){successTitle.innerHTML = "{$successTitle}";}
var contractSpan = document.getElementById('ls_cashpresso_contract_text');
if (typeof(contractSpan) != "undefined"){contractSpan.innerHTML = "{$successMessage}";}
var btnPrimary = document.getElementsByClassName("c2-btn-primary");
if (btnPrimary.length){if (typeof btnPrimary[0] != "undefined") {btnPrimary[0].innerHTML = "$successButtonTitle";}}
}}
//]]>
</script> 
<script id="c2PostCheckoutScript" type="text/javascript"
    src="{$jsScr}"
    defer
    data-c2-partnerApiKey="{$apiKey}"
    data-c2-purchaseId="{$purchaseId}"
    data-c2-mode="{$mode}"
    data-c2-locale="{$locale}"
    data-c2-successCallback="true"
></script>
EOT;
            $helper = Mage::helper('cms');
            $processor = $helper->getPageTemplateProcessor();
            $message = $processor->filter($this->_helper()->getContractText());

            $transport->setHtml(str_replace("{{cashpresso}}", $script . "<p class=\"a-center\" style=\"margin-top:10px;\" id=\"ls_cashpresso_contract_text\">{$message}</p>", $html));
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

    /**
     * @param Varien_Event_Observer $observer
     */
    public function defineTemplate(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Checkout_Block_Onepage_Success) {

            if (!$this->_helper()->getAPIKey()) {
                return;
            }

            if ($order = Mage::registry('ls_success_order')) {
                $additionalData = json_decode($order->getPayment()->getAdditionalData(), true);
                if (isset($additionalData['cashpresso'])) {
                    $block->setTemplate('limesoda/cashpresso/checkout/success.phtml');
                } else {
                    Mage::unregister('ls_success_order');
                }
            }
        }
    }
}