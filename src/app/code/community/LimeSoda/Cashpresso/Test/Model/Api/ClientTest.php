<?php



class LimeSoda_Cashpresso_Test_Model_Api_ClientTest extends EcomDev_PHPUnit_Test_Case
{
    protected $_apiClient;

    public function setUp()
    {
        $this->_apiClient = Mage::getModel('ls_cashpresso/api_client');
    }

    public function testGetPartnerInfo()
    {
        $this->assertEquals('https://test.cashpresso.com/rest/backend/ecommerce/v2/', $this->_apiClient->getTestUrl(), "test api link is wrong");
        $this->assertEquals('https://backend.cashpresso.com/rest/backend/ecommerce/v2/', $this->_apiClient->getLiveUrl(), "live api link is wrong");
    }
}