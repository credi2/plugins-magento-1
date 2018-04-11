<?php


class LimeSoda_Cashpresso_Model_Adminhtml_System_Config_Source_Account
{

    public function getTargetAccounts()
    {
        return Mage::getModel('ls_cashpresso/api_account')->getTargetAccounts();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $accounts = $this->getTargetAccounts();

        $list = array();

        foreach ($accounts as $account) {
            $list[] = array(
                'value' => $account['targetAccountId'],
                'label' => $account['holder']
            );
        }

        array_unshift($list, array('value' => '', 'label' => Mage::helper('ls_cashpresso')->__('-- Not Selected --')));

        return $list;
    }

    /**⁄
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $accounts = $this->getTargetAccounts();

        $list = array();

        foreach ($accounts as $account) {
            $list[$account['targetAccountId']] = $account['holder'];
        }

        array_unshift($list, array('' => Mage::helper('ls_cashpresso')->__('-- Not Selected --')));

        return $list;
    }
}