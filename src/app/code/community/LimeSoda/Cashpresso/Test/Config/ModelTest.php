<?php


class LimeSoda_Cashpresso_Test_Config_ModelTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testModel()
    {
        $this->assertModelAlias('ls_cashpresso/customer', 'LimeSoda_Cashpresso_Model_Customer');
    }
}