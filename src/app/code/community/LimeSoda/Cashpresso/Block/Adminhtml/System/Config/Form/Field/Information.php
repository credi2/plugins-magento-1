<?php


class LimeSoda_Cashpresso_Block_Adminhtml_System_Config_Form_Field_Information
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @throws Exception
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $text = '';

        if (Mage::helper('ls_cashpresso')->getAPIKey()) {
            $partnerInfo = Mage::helper('ls_cashpresso')->generatePartnerInfo();
        } else {
            $text = Mage::helper('ls_cashpresso')->__('Please, enter the Partner API Key.');
        }

        if (!empty($partnerInfo) && is_array($partnerInfo) && !empty($partnerInfo['success'])) {
            $list = array();

            if (isset($partnerInfo['companyName'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Company name'),
                    'value' => $this->escapeHtml($partnerInfo['companyName']));
            }

            if (isset($partnerInfo['email'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Email'),
                    'value' => $this->escapeHtml($partnerInfo['email']));
            }

            if (isset($partnerInfo['holder'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Holder'),
                    'value' => $this->escapeHtml($partnerInfo['holder'])
                );
            }

            if (isset($partnerInfo['iban'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Iban'),
                    'value' => $this->escapeHtml($partnerInfo['iban'])
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
                    'value' => $this->escapeHtml($partnerInfo['status'])
                );
            }

            if (isset($partnerInfo['currency'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Currency'),
                    'value' => $this->escapeHtml($partnerInfo['currency'])
                );
            }

            if (isset($partnerInfo['minPaybackAmount'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Minimal payback amount'),
                    'value' => $this->escapeHtml($partnerInfo['minPaybackAmount'])
                );
            }

            if (isset($partnerInfo['paybackRate'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Payback rate'),
                    'value' => $this->escapeHtml($partnerInfo['paybackRate'])
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
                    'value' => $this->escapeHtml($partnerInfo['interest']['nominal']['min'])
                        . " - " . $this->escapeHtml($partnerInfo['interest']['nominal']['max'])
                );
            }

            if (isset($partnerInfo['interest'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Interest effective'),
                    'value' => $this->escapeHtml($partnerInfo['interest']['effective']['min'])
                        . " - " . $this->escapeHtml($partnerInfo['interest']['effective']['max'])
                );
            }

            if (isset($partnerInfo['interestFreeCashpresso'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Interest Free cashpresso'),
                    'value' => (int)$partnerInfo['interestFreeCashpresso']
                );
            }

            if (isset($partnerInfo['last_update'])) {
                $list[] = array(
                    'title' => $this->helper('ls_cashpresso')->__('Last Update'),
                    'value' => $this->escapeHtml($partnerInfo['last_update'])
                );
            }

            $text = '';

            foreach ($list as $item) {
                $text .= "<p><label style='font-weight: bold'>{$item['title']}:</label> {$item['value']}</p>";
            }
        }

        return '<div id="' . $element->getHtmlId() . '">' .
            $text . '</div>' . $element->getAfterElementHtml();
    }
}