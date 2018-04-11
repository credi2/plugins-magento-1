<?php



class LimeSoda_Cashpresso_Test_Config_BlockTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testBlock()
    {
        $this->assertHelperAlias('ls_cashpresso', 'LimeSoda_Cashpresso_Helper_Data');
    }
}