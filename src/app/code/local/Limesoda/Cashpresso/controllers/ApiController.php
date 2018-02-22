<?php
/**
 * 21.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file ${FILE_NAME}
 */

class Limesoda_Cashpresso_ApiController extends Mage_Core_Controller_Front_Action
{
    public function callbackAction()
    {
        if (!Mage::helper('ls_cashpresso')->getMode()) {
            Mage::getModel('ls_cashpresso/api_client')->sendSimulationCallbackRequest(
                trim(strtoupper($this->getRequest()->getParam('type'))),
                $this->getRequest()->getParam('purchaseID')
            );
            exit;
        } else {
            $this->_redirectError();
        }

    }

    public function indexAction()
    {
        if (!Mage::helper('ls_cashpresso')->getMode()) {
            print Mage::getUrl('cashpresso/api/callback', array(
                'type' => strtolower(Limesoda_Cashpresso_Model_Api_Client::CODE_SIMULATION_SUCCESS),
                'purchaseID' => 'SIM-...0102'
            ));
            exit;
        } else {
            $this->_redirectError();
        }
    }
}