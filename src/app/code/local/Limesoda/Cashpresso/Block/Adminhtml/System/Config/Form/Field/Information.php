<?php
/**
 * 15.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Information.php
 */

class Limesoda_Cashpresso_Block_Adminhtml_System_Config_Form_Field_Information extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @throws Exception
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $partnerInfo = Mage::helper('ls_cashpresso')->getPartnerInfo();

        $text = '';

        if (is_null($partnerInfo)) {
            if (!Mage::helper('ls_cashpresso')->getAPIKey()) {
                $text = Mage::helper('ls_cashpresso')->__('Please, enter the Partner API Key.');
            } else {
                $partnerInfo = Mage::helper('ls_cashpresso')->generatePartnerInfo();
            }
        }

        if (is_array($partnerInfo) && !empty($partnerInfo['success'])) {
            $list = [];

            if (isset($partnerInfo['companyName'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Company name'),
                    'value' => $partnerInfo['companyName']
                );
            }
            if (isset($partnerInfo['email'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Email'),
                    'value' => $partnerInfo['email']
                );
            }

            if (isset($partnerInfo['holder'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Holder'),
                    'value' => $partnerInfo['holder']
                );
            }

            if (isset($partnerInfo['iban'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Iban'),
                    'value' => $partnerInfo['iban']
                );
            }

            if (isset($partnerInfo['interestFreeEnabled'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Interest Free Status'),
                    'value' => (bool)$partnerInfo['interestFreeEnabled']
                );
            }

            if (isset($partnerInfo['interestFreeMaxDuration'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Interest Free Max Duration'),
                    'value' => (bool)$partnerInfo['interestFreeMaxDuration']
                );
            }

            if (isset($partnerInfo['status'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Status'),
                    'value' => $partnerInfo['status']
                );
            }

            if (isset($partnerInfo['currency'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Currency'),
                    'value' => $partnerInfo['currency']
                );
            }

            if (isset($partnerInfo['minPaybackAmount'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Minimal payback amount'),
                    'value' => $partnerInfo['minPaybackAmount']
                );
            }

            if (isset($partnerInfo['paybackRate'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Payback rate'),
                    'value' => $partnerInfo['paybackRate']
                );
            }

            if (isset($partnerInfo['limit']['financing'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Financing limit'),
                    'value' => (int)$partnerInfo['limit']['financing']
                );
            }

            if (isset($partnerInfo['limit']['prepayment'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Prepayment limit'),
                    'value' => (int)$partnerInfo['limit']['prepayment']
                );
            }

            if (isset($partnerInfo['limit']['total'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Total limit'),
                    'value' => (int)$partnerInfo['limit']['total']
                );
            }

            if (isset($partnerInfo['interest']['nominal'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Interest nominal'),
                    'value' => $partnerInfo['interest']['nominal']['min'] . " - " . $partnerInfo['interest']['nominal']['max']
                );
            }

            if (isset($partnerInfo['interest'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Interest effective'),
                    'value' => $partnerInfo['interest']['effective']['min'] . " - " . $partnerInfo['interest']['effective']['max']
                );
            }

            if (isset($partnerInfo['interestFreeCashpresso'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Interest Free Cashpresso'),
                    'value' => (int)$partnerInfo['interestFreeCashpresso']
                );
            }

            $text = '';

            foreach ($list as $item) {
                $text .= "<p><label style='font-weight: bold'>{$item['title']}:</label> {$item['value']}</p>";
            }
        }

        return '<div id="'.$element->getHtmlId().'">'.$text.'</div>'  . $element->getAfterElementHtml();
    }
}