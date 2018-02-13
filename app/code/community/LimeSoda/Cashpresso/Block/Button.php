<?php


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
        $checkResult->types = $this->_helper()->getProductTypes();
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

    protected function getPrice($_product)
    {
        switch ($_product->getTypeId()) {
            case 'bundle':
                /** @var Mage_Bundle_Model_Product_Price $_priceModel */
                $_priceModel = $_product->getPriceModel();

                $price = $_priceModel->getTotalPrices($_product, 'min', null, null);

                /*if (!in_array('PRODUCT_TYPE_bundle', Mage::app()->getLayout()->getUpdate()->getHandles())) {
                    $price = 0;
                }*/
                break;
            case 'grouped':
                /** @var Mage_Catalog_Model_Product_Type_Grouped_Price $_priceModel */
                //$_priceModel = $_product->getPriceModel();
                $price = $_product->getMinimalPrice();

                break;
            default:
                $price = $_product->getFinalPrice();
        }

        /** @var Mage_Core_Helper_Data $_coreHelper */
        $_coreHelper = $this->helper('core');
        return $_coreHelper->currency($price, false, false);
    }

    protected function _toHtml()
    {
        if (!$this->_helper()->checkStatus()) {
            return '';
        }

        /** @var Mage_Catalog_Model_Product $productModel */
        $productModel = $this->getProduct() ?: Mage::registry('current_product');

        if (!($productModel && $this->isAvailable($productModel))) {
            return '';
        }

        if ($productModel->getMsrp() || (!$price = $this->getPrice($productModel))) {
            return '';
        }

        if ($price >= $this->_helper()->getTotalLimit()) {
            return '';
        }

        if ($price <= $this->_helper()->getMinLimit()) {
            return '';
        }

        $widgetProductLevelIntegration = $this->_helper()->getWidgetType();

        $htmlEntry = '';

        if ($widgetProductLevelIntegration) {
            $htmlEntry = '<div class="c2-financing-label" data-c2-financing-amount="' . $price . '"></div>';
        } else {
            $minPayment = $this->_helper()->getDebt($price);

            if ($minPayment > 0) {
                $template = strpos($this->_helper()->getTemplate(), '{{price}}') !== false ?
                    $this->_helper()->getTemplate() :
                    $this->_helper()->__("or from € {{price}} / month");
                $aText = preg_replace("/{{price}}/", $minPayment, $template);

                $htmlEntry = '<a id="cashpresso_product_id_' . $productModel->getId() . '" href="#" onclick="C2EcomWizard.startOverlayWizard(' . $price . ')">' . $aText . '</a>';
            }
        }

        return $htmlEntry;
    }
}