<?php


class LimeSoda_Cashpresso_Block_Checkout extends Mage_Core_Block_Template
{
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    protected function _toHtml()
    {
        if (!$this->_helper()->checkStatus(false)) {
            return '';
        }

        if (!$price = Mage::app()->getStore()->roundPrice(Mage::getModel('checkout/cart')->getQuote()->getGrandTotal())) {
            return '';
        }

        $apiKey = $this->_helper()->getAPIKey();

        $mode = $this->_helper()->getMode() ? 'live' : 'test';

        $customerData = Mage::getModel('ls_cashpresso/customer')->getCustomerData();

        list($locale) = explode('_', strtolower(Mage::app()->getLocale()->getLocaleCode()));

        $interestFreeDays = $this->_helper()->getInterestFreeDay();

        $jsScr = $this->_helper()->getJsCheckoutScript();

        /**
         * country  = at|de
         * mode
         * locale⁄
         *!
         */
        $cashPressoButton = <<<EOT
<script id="c2CheckoutScript" type="text/javascript" 
    src="{$jsScr}" 
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