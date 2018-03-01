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
 * @file BlockTest.php
 */


class LimeSoda_Cashpresso_Test_Config_BlockTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testBlock()
    {
        $this->assertHelperAlias('ls_cashpresso', 'LimeSoda_Cashpresso_Helper_Data');
    }
}