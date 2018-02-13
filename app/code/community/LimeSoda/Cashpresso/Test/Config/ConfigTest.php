<?php


class LimeSoda_Cashpresso_Test_Config_ConfigTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testDefaultValues()
    {
        $this->assertConfigNodeValue('default/payment/cashpresso/model', 'ls_cashpresso/payment_method_cashpresso');
    }

    public function testObservers()
    {
        $this->assertEventObserverDefined('frontend', 'sales_quote_payment_import_data_before', 'ls_cashpresso/observer_payment', 'addPostHashOrder', 'limesoda_cashpresso');
        $this->assertEventObserverDefined('frontend', 'sales_order_payment_place_end', 'ls_cashpresso/observer_payment', 'sendOrder', 'limesoda_cashpresso');
        $this->assertEventObserverDefined('frontend', 'checkout_onepage_controller_success_action', 'ls_cashpresso/observer_block', 'setOrder', 'ls_cashpresso_order_register');
        $this->assertEventObserverDefined('frontend', 'core_block_abstract_to_html_after', 'ls_cashpresso/observer_block', 'coreBlockAbstractToHtmlAfter', 'ls_cashpresso_before_print');
    }

    public function testLayout()
    {
        $this->assertLayoutFileExistsInTheme('frontend', 'limesoda/cashpresso.xml', 'default', 'base');
        $this->assertLayoutFileDefined('frontend', 'limesoda/cashpresso.xml', 'limesoda_cashpresso');
        $this->assertLayoutFileExists('frontend', 'limesoda/cashpresso.xml');
    }
}