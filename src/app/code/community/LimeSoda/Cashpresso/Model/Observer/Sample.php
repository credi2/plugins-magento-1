<?php
/**
 * 22.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Sample.php
 */

class LimeSoda_Cashpresso_Model_Observer_Sample
{
    public function addType(Varien_Event_Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $product = $observer->getEvent()->getProduct();

        array_push($result->types, 'configurable');
    }
}