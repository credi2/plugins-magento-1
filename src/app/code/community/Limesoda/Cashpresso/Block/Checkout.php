<?php
/**
 * 19.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    Limesoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Checkout.php
 */

class Limesoda_Cashpresso_Block_Checkout extends Mage_Core_Block_Template
{
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    protected function _toHtml()
    {
        if (!$this->_helper()->isModuleEnabled() ||
            !Mage::getModel('ls_cashpresso/payment_method_cashpresso')->getConfigData('active') ||
            !$apiKey = $this->_helper()->getAPIKey()) {
            return '';
        }
        
        $mode = $this->_helper()->getMode() ? 'live' : 'test';

        $customerData = Mage::getModel('ls_cashpresso/customer')->getCustomerData();

        $price = Mage::app()->getStore()->roundPrice(Mage::getModel('checkout/cart')->getQuote()->getGrandTotal());

        list($locale) = explode('_', strtolower(Mage::app()->getLocale()->getLocaleCode()));

        $interestFreeDays = $this->_helper()->getInterestFreeDay();

        /**
         * country  = at|de
         * mode
         * locale
         *
         */
        $cashPressoButton = <<<EOT
<script id="c2CheckoutScript" type="text/javascript" 
    src="https://my.cashpresso.com/ecommerce/v2/checkout/c2_ecom_checkout.all.min.js" 
    defer
    data-c2-partnerApiKey="{$apiKey}" 
    data-c2-interestFreeDaysMerchant="{$interestFreeDays}"
    data-c2-mode="{$mode}" 
    data-c2-locale="{$locale}"
    data-c2-email="{$customerData->getEmail()}"
    data-c2-given="{$customerData->getFirstname()}"
    data-c2-family="{$customerData->getLastname()}"
    data-c2-birthdate="{$customerData->getDob()}"
    data-c2-country="{$customerData->getCountryCode()}"
    data-c2-city="{$customerData->getCity()}"
    data-c2-zip="{$customerData->getPostcode()}"
    data-c2-addressline="{$customerData->getStreet()}"
    data-c2-phone="{$customerData->getTelephone()}"
    data-c2-iban="{$customerData->getTaxvat()}"
    data-c2-amount="{$price}">
  </script>

<script type="text/javascript">
    //<![CDATA[
    Payment.prototype.save = function () {
        if (checkout.loadWaiting!=false) return;
    
        var validator = new Validation(this.form);
    
        if (this.validate() && validator.validate()) {
            checkout.setLoadWaiting('payment');
            document.getElementById('cashpressoToken').disabled = false;
            new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }    
    };
    //]]>
</script> 
EOT;

        return $cashPressoButton;
    }
}