<?php
/**
 * 28.02.18
 * LimeSoda - dockerized-magento (Cashpresso)
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento (Cashpresso)
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file DataTest.php
 */

class LimeSoda_Cashpresso_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * @test
     * @loadFixture config
     */
    public function getTimeout()
    {
        //$this->assertConfigNodeValue('payment/cashpresso/timeout', '2');


        $time = DateTime::createFromFormat('Y-m-d H:i:s', '2017-08-31 00:00:00');

        $timout = Mage::helper('ls_cashpresso')->getConvertTime($time->getTimestamp(), 2);
        $expected = '2017-08-31T02:00:00+00:00';

        $this->assertEquals($expected, $timout);
    }
}