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
 * @file Button.php
 */

class LimeSoda_Cashpresso_Block_Button extends Mage_Core_Block_Template
{
    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @return mixed
     */
    public function isAvailable($product)
    {
        $checkResult = new StdClass;
        $checkResult->types = array('simple');
        $checkResult->isAvailable = true;

        Mage::dispatchEvent('cashpresso_type_handler', array(
            'product' => $product,
            'result' => $checkResult
        ));

        if (is_array($checkResult->types) && !in_array($product->getTypeId(), $checkResult->types)) {
            $checkResult->isAvailable = false;
        }

        return $checkResult->isAvailable;
    }

    /**
     * Emulation to avoid toHtml call, which remove the instance of parent transport object in
     * app/code/local/LimeSoda/Cashpresso/Model/Observer/Block.php::addScriptToPrice
     *
     * @return string
     */
    public function emulateToHtml()
    {
        return $this->_toHtml();
    }

    protected function _toHtml()
    {
        if (!$this->_helper()->checkStatus()) {
            return '';
        }

        /** @var Mage_Catalog_Model_Product $productModel */
        $productModel = $this->getProduct() ?: Mage::registry('current_product');

        if ($productModel) {
            if (!$this->isAvailable($productModel)) {
                return '';
            }

            $price = $productModel->getFinalPrice();
        } else {
            return '';
        }

        $apiKey = $this->_helper()->getAPIKey();

        $widgetProductLevelIntegration = $this->_helper()->getWidgetType();

        $htmlEntry = '';

        if ($widgetProductLevelIntegration) {
            $htmlEntry = '<div class="c2-financing-label" data-c2-financing-amount="' . $price . '"></div>';
        } else {
            $partnerInfo = $this->_helper()->getPartnerInfo();

            $minPayment = Mage::helper('ls_cashpresso/request')->getDebt($partnerInfo, $price);

            if ($minPayment > 0) {
                $template = strpos($this->_helper()->getTemplate(), '{{price}}') !== false ?
                    $this->_helper()->getTemplate() :
                    $this->_helper()->__("or from € {{price}} / month");
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

        $checkoutButton = $this->_helper()->showCheckoutButton() && Mage::helper('ls_cashpresso/request')->isProductPage() ? 'true' : 'false';

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
    data-c2-checkoutCallback="{$checkoutButton}">
  </script>
EOT;

        return $cashPressoButton;
    }
}