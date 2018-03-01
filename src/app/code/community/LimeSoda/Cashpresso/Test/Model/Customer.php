<?php
/**
 * 01.03.18
 * LimeSoda - dockerized-magento (Cashpresso)
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento (Cashpresso)
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Customer.php
 */

class LimeSoda_Cashpresso_Test_Model_Customer extends EcomDev_PHPUnit_Test_Case
{
    protected $_customer;

    public function setUp()
    {
        $this->_customer = Mage::getModel('ls_cashpresso/customer');
    }

    public function testGetCustomerData()
    {

    }

    protected function _getCustomerData($quotePriority = false)
    {
        $customerData = new Varien_Object();

        if (!$quotePriority && Mage::getModel('customer/session')->getCustomer()->getId()) {
            $customer = Mage::getModel('customer/session')->getCustomer();

            $customerData->setEmail($customer->getEmail());
            $customerData->setFirstname($customer->getFirstname());
            $customerData->setLastname($customer->getLastname());
            $customerData->setDob($customer->getDob());
            $customerData->setTaxvat($customer->getTaxvat());

            $billingAddressId = $customer->getDefaultBilling();
            $shippingAddressId = $customer->getDefaultShipping();

            $billingAddress = Mage::getModel('customer/address')->load($billingAddressId);
            $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);

            $customer->getStoreId();
            $customer->getWebsiteId();
        } else {
            $cart = Mage::getModel('checkout/cart')->getQuote();

            $billingAddress = $cart->getBillingAddress();
            $shippingAddress = $cart->getShippingAddress();

            $customerData->setEmail($billingAddress->getEmail() ? $billingAddress->getEmail() : $shippingAddress->getEmail());
            $customerData->setFirstname($billingAddress->getFirstname() ? $billingAddress->getFirstname() : $shippingAddress->getFirstname());
            $customerData->setLastname($billingAddress->getLastname() ? $billingAddress->getLastname() : $shippingAddress->getLastname());
        }

        $customerData->setCity($billingAddress->getCity() ? $billingAddress->getCity() : $shippingAddress->getCity());
        $customerData->setCountryCode(strtolower($billingAddress->getCountryId() ? $billingAddress->getCountryId() : $shippingAddress->getCountryId()));
        $customerData->setPostcode($billingAddress->getPostcode() ? $billingAddress->getPostcode() : $shippingAddress->getPostcode());
        $customerData->setTelephone($billingAddress->getTelephone() ? $billingAddress->getTelephone() : $shippingAddress->getTelephone());

        $street = $billingAddress->getStreet() ? $billingAddress->getStreet() : $shippingAddress->getStreet();
        $customerData->setStreet(isset($street[0]) ? $street[0] : '');

        return $customerData;
    }
}