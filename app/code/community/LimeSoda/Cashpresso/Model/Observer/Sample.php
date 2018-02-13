<?php


class LimeSoda_Cashpresso_Model_Observer_Sample
{
    public function addType(Varien_Event_Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $product = $observer->getEvent()->getProduct();

        array_push($result->types, 'configurable');
    }
}