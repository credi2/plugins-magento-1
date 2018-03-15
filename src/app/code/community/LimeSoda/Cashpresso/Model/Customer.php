<?php
/**
 * 16.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Customer.php
 */

class LimeSoda_Cashpresso_Model_Customer
{
    public function getCustomerData($quotePriority = false)
    {
        $customerData = new Varien_Object();

        $billingAddress = $shippingAddress = new Varien_Object();

        if (!$quotePriority && Mage::getModel('customer/session')->getCustomer()->getId()) {
            $customer = Mage::getModel('customer/session')->getCustomer();

            $dob = $customer->getDob() ? Mage::getSingleton('core/date')->date('Y-m-d', $customer->getDob()) : null;

            $customerData->setEmail($customer->getEmail());
            $customerData->setFirstname($customer->getFirstname());
            $customerData->setLastname($customer->getLastname());
            $customerData->setDob($dob);
            $customerData->setTaxvat($customer->getTaxvat());

            $billingAddressId = $customer->getDefaultBilling();
            $shippingAddressId = $customer->getDefaultShipping();

            /** @var Mage_Sales_Model_Quote_Address $billingAddress */
            $billingAddress = Mage::getModel('customer/address')->load($billingAddressId);
            /** @var Mage_Sales_Model_Quote_Address $shippingAddress */
            $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
        }

        $cart = Mage::getModel('checkout/cart')->getQuote();

        if (!$billingAddress->getId() && !$shippingAddress->getId()) {
            $billingAddress = $cart->getBillingAddress();
            $shippingAddress = $cart->getShippingAddress();
        }

        if (!$customerData->getEmail()){
            $customerData->setEmail($billingAddress->getEmail() ? $billingAddress->getEmail() : $shippingAddress->getEmail());
        }

        if (!$customerData->getFirstname()){
            $customerData->setFirstname($billingAddress->getFirstname() ? $billingAddress->getFirstname() : $shippingAddress->getFirstname());
        }

        if (!$customerData->getLastname()){
            $customerData->setLastname($billingAddress->getLastname() ? $billingAddress->getLastname() : $shippingAddress->getLastname());
        }

        $customerData->setCity($billingAddress->getCity() ? $billingAddress->getCity() : $shippingAddress->getCity());
        $customerData->setCountryCode(strtolower($billingAddress->getCountryId() ? $billingAddress->getCountryId() : $shippingAddress->getCountryId()));
        $customerData->setPostcode($billingAddress->getPostcode() ? $billingAddress->getPostcode() : $shippingAddress->getPostcode());
        $customerData->setTelephone($billingAddress->getTelephone() ? $billingAddress->getTelephone() : $shippingAddress->getTelephone());

        $street = $billingAddress->getStreetFull() ? $billingAddress->getStreetFull() : $shippingAddress->getStreetFull();
        $customerData->setStreet(str_replace("\n", ", ", $street));

        return $customerData;
    }
}