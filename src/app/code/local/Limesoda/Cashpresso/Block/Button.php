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
    protected function _toHtml()
    {
        if (!Mage::helper('ls_cashpresso')->isModuleEnabled() || !Mage::helper('ls_cashpresso')->getStatus() || !$apiKey = Mage::helper('ls_cashpresso')->getAPIKey()) {
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

        if (Mage::helper('ls_cashpresso')->getMode()) {

        } else {
            $htmlEntry = '<div class="c2-financing-label" data-c2-financing-amount="'.$price.'"></div>';
        }

        /**
         * country  = at|de
         * mode
         * locale
         *
         */
        $cashPressoButton = <<<EOT
{$htmlEntry}
  <script id="c2LabelScript" type="text/javascript" 
    src="https://my.cashpresso.com/ecommerce/v2/label/c2_ecom_wizard.all.min.js" 
    defer
    data-c2-partnerApiKey="{$apiKey}" 
    data-c2-interestFreeDaysMerchant="0"
    data-c2-mode="test" 
    data-c2-locale="en"
    data-c2-email="a.sannikov@limesoda.com  "
    data-c2-given="Anton"
    data-c2-family="Sannikov"
    data-c2-birthdate="1950-01-01"
    data-c2-country="at"
    data-c2-city="Wien"
    data-c2-zip="1170"
    data-c2-addressline="Syringasse"
    data-c2-phone="+4366666666666"
    data-c2-iban="1234 1234 1234 12345">
  </script>
EOT;

        return $cashPressoButton;
    }
}