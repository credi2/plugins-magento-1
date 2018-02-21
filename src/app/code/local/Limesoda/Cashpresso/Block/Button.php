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
 * @file Button.php
 */

class Limesoda_Cashpresso_Block_Button extends Mage_Core_Block_Template
{
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    protected function _toHtml()
    {
        if (!$this->_helper()->isModuleEnabled() || !Mage::getModel('ls_cashpresso/payment_method_cashpresso')->getConfigData('active') || !$this->_helper()->getStatus() || !$apiKey = $this->_helper()->getAPIKey()) {
            return '';
        }

        if (Mage::registry('current_product')) {
            /** @var Mage_Catalog_Model_Product $productModel */
            $productModel = Mage::registry('current_product');

            if (!in_array($productModel->getTypeId(), array('configurable', 'simple'))) {
                return '';
            }

            $price = $productModel->getFinalPrice();
        } else {
            return '';
        }

        $widgetProductLevelIntegration = $this->_helper()->getWidgetType();

        if ($widgetProductLevelIntegration) {
            $htmlEntry = '<div class="c2-financing-label" data-c2-financing-amount="' . $price . '"></div>';
        } else {
            $partnerInfo = $this->_helper()->getPartnerInfo();

            $minPayment = 0;

            if (isset($partnerInfo['minPaybackAmount']) && isset($partnerInfo['paybackRate'])) {
                $minPayment = min($price, max($partnerInfo['minPaybackAmount'], $price * 0.01 * $partnerInfo['paybackRate']));
            }

            if ($minPayment > 0) {
                $template = strpos($this->_helper()->getTemplate(), '{{price}}') !== false ? $this->_helper()->getTemplate() : $this->_helper()->__("or from € {{price}} / month");
                $aText = preg_replace("/{{price}}/", $minPayment, $template);

                $htmlEntry = '<a href="#" onclick="C2EcomWizard.startOverlayWizard(' . $price . ')">' . $aText . '</a>';
            }
        }

        $mode = $this->_helper()->getMode() ? 'live' : 'test';

        $customerData = Mage::getModel('ls_cashpresso/customer')->getCustomerData();

        $idStatic = !$widgetProductLevelIntegration ? 'Static' : '';
        $scriptStatic = !$widgetProductLevelIntegration ? '_static' : '';

        list($locale) = explode('_', strtolower(Mage::app()->getLocale()->getLocaleCode()));

        $interestFreeDays = $this->_helper()->getInterestFreeDay();

        /**
         * country  = at|de
         * mode
         * locale
         *
         */
        $cashPressoButton = <<<EOT
{$htmlEntry}
  <script id="c2{$idStatic}LabelScript" type="text/javascript" 
    src="https://my.cashpresso.com/ecommerce/v2/label/c2_ecom_wizard{$scriptStatic}.all.min.js" 
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
    data-c2-checkoutCallback="true">
  </script>
EOT;

        return $cashPressoButton;
    }
}