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
        /**
         * country  = at|de
         * mode
         * locale
         *
         */
        $cashPressoButton = <<<EOT
  <div class="c2-financing-label" data-c2-financing-amount="1500.00"></div> 

  <script id="c2LabelScript" type="text/javascript" 
    src="https://my.cashpresso.com/ecommerce/v2/label/c2_ecom_wizard.all.min.js" 
    defer
    data-c2-partnerApiKey="908db109be694529dd0c1331afe4e5ae74b41176c3d64dea99f798ff8f7cc7ab622de47a92d52a560a971e7d0ce68b0ea0d1bab243fc859bf80f76e93987fbe2" 
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