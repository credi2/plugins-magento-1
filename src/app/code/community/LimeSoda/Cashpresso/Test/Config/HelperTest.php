<?php


class LimeSoda_Cashpresso_Test_Config_HelperTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testHelper()
    {
        $this->assertHelperAlias('ls_cashpresso', 'LimeSoda_Cashpresso_Helper_Data');
    }
}