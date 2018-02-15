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
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        $model = Mage::getModel('ls_cashpresso/api');

        $model->setMode(true);
        
        $info = $model->getPartnerInfo();

        echo '<pre><br/>';
        var_dump($info);
        die();
        die();
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Catalog_Block_Product_Abstract) {
            $htmlBlock = $observer->getEvent()->getTransport();

            $cashPressoButton = <<<EOT
  <div class="c2-financing-label" data-c2-financing-amount="1500.00"></div> 

  <script id="c2LabelScript" type="text/javascript" 
    src="https://my.cashpresso.com/ecommerce/v2/label/c2_ecom_wizard.all.min.js" 
    defer
    data-c2-partnerApiKey="908db109be694529dd0c1331afe4e5ae74b41176c3d64dea99f798ff8f7cc7ab622de47a92d52a560a971e7d0ce68b0ea0d1bab243fc859bf80f76e93987fbe2" 
    data-c2-interestFreeDaysMerchant="0"
    data-c2-mode="test" 
    data-c2-locale="en">
  </script>
EOT;

            $htmlBlock->setHtml($htmlBlock->getHtml() . $cashPressoButton);
        }
    }
}