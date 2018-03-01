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
 * @file CustomerTest.php
 */

class LimeSoda_Cashpresso_Test_Config_ModelTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testModel()
    {
        $this->assertModelAlias('ls_cashpresso/customer', 'LimeSoda_Cashpresso_Model_Customer');
    }
}