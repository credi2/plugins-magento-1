<?php
/**
 * 15.03.18
 * LimeSoda - as
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     as
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Currency.php
 */

class LimeSoda_Cashpresso_Block_Adminhtml_System_Config_Form_Field_Currency
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_localCurrency = null;

    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @throws Exception
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $text = $this->_helper()->__("cashpresso account currency (%s) does not match to store currency (%s).", $this->_helper()->getContractCurrency(), $this->getStoreCurrency());

        if ($this->checkCurrency() === false) {
            return '<div id="' . $element->getHtmlId() . '">' .
                $text . '</div>' . $element->getAfterElementHtml();
        }

        return '';
    }

    /**
     * @return null|string
     */
    protected function getStoreCurrency()
    {
        if ($this->_localCurrency === null) {
            list($websiteId, $currentStoreId) = $this->_helper()->getCurrentStore();

            $this->_localCurrency = Mage::app()->getStore($currentStoreId)->getCurrentCurrencyCode();
        }

        return $this->_localCurrency;
    }

    /**
     * Decorate field row html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        if ($this->checkCurrency() === false) {
            return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
        }

        return '';
    }

    /**
     * @return bool|null
     */
    protected function checkCurrency()
    {
        if (!$contractCurrency = $this->_helper()->getContractCurrency()) {
            return null;
        }

        return $this->getStoreCurrency() == $contractCurrency;
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $html = '<td class="label" colspan="3"><div style="color:red; font-weight: bold; padding-bottom: 40px;" for="'.$id.'">'.$this->_getElementHtml($element).'</div></td>';

        //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType()==='multiple';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = $this->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = $this->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif (isset($v['value'])) {
                        if ($v['value'] == $defText) {
                            $defTextArr[] = $v['label'];
                            break;
                        }
                    } elseif (!is_array($v)) {
                        if ($k == $defText) {
                            $defTextArr[] = $v;
                            break;
                        }
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html.= '<td class="use-default">';
            $html.= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html.= '</td>';
        }

        $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td>';

        $html.= '<td class="">';
        if ($element->getHint()) {
            $html.= '<div class="hint" >';
            $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html.= '</div>';
        }
        $html.= '</td>';

        return $this->_decorateRowHtml($element, $html);
    }
}