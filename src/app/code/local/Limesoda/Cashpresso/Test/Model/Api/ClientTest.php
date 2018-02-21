<?php
/**
 * 15.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file ApiTest.php
 */


class Limesoda_Cashpresso_Test_Model_Api_ClientTest extends EcomDev_PHPUnit_Test_Case
{
    protected $apiClient;

    public function setUp()
    {
        $this->apiClient = Mage::getModel('ls_cashpresso/api_client');
    }

    public function testGetPartnerInfo()
    {
        $this->assertEquals('https://test.cashpresso.com/rest/backend/ecommerce/v2/', $this->apiClient->getTestUrl(), "test api link is wrong");
        $this->assertEquals('https://backend.cashpresso.com/rest/backend/ecommerce/v2/', $this->apiClient->getLiveUrl(), "live api link is wrong");
    }
}