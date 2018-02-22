<?php
/**
 * 22.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Sample.php
 */

class Limesoda_Cashpresso_Model_Observer_Sample
{
    public function addType(Varien_Event_Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $product = $observer->getEvent()->getProduct();

        array_push($result->types, 'configurable');
    }
}