<?php
/**
 * 21.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file app/code/local/LimeSoda/Cashpresso/controllers/ApiController.php
 */

class LimeSoda_Cashpresso_ApiController extends Mage_Core_Controller_Front_Action
{
    protected function getHelper()
    {
        return Mage::helper('ls_cashpresso');
    }

    public function callbackAction()
    {
        try {
            $response = Mage::helper('core')->jsonDecode($this->getRequest()->getRawBody());

            if ($this->getHelper()->isDebugEnabled()){
                Mage::log(print_r($response, true), Zend_Log::DEBUG, 'cashpresso.log');
            }

            if (Mage::helper('ls_cashpresso/request')->hashCheck($response, $this->getHelper()->getSecretKey())) {
                throw new Exception($this->getHelper()->__("Verification hash is wrong."));
            }

            if (empty($response['usage'])) {
                throw new Exception($this->getHelper()->__("Order ID is empty."));
            }

            $order = Mage::getModel('sales/order')->loadByIncrementId($response['usage']);

            if (!$order->getId()) {
                throw new Exception($this->getHelper()->__("Order %d was not found", $order->getId()));
            }

            switch ($response['status']) {
                case 'SUCCESS':
                    $orderState = $order->getState();
                    if ($orderState === Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                        if ($order->canInvoice()) {
                            $invoice = $order->prepareInvoice();
                            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                            $invoice->register();

                            Mage::getModel('core/resource_transaction')
                                ->addObject($invoice)
                                ->addObject($order)
                                ->save();

                            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                        }
                    }
                    break;
                case 'CANCELLED':
                case 'TIMEOUT':
                    $orderState = $order->getState();

                    if ($orderState === Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                        $order->cancel()->save();
                    }
                    break;
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->norouteAction();
        }
    }

    public function testAction()
    {
        if (!Mage::helper('ls_cashpresso')->getMode()) {
            try{
                Mage::getModel('ls_cashpresso/api_client')->sendSimulationCallbackRequest(
                    trim(strtoupper($this->getRequest()->getParam('type'))),
                    $this->getRequest()->getParam('purchaseID')
                );
            } catch (Exception $e){
                if ($this->getHelper()->isDebugEnabled()){
                    Mage::log($e->getMessage(), Zend_Log::DEBUG, 'cashpresso.log');
                } else {
                    Mage::logException($e);
                }
            }
        } else {
            $this->norouteAction();
        }
    }

    public function indexAction()
    {
        if (!Mage::helper('ls_cashpresso')->getMode()) {
            echo Mage::getUrl('cashpresso/api/test', array(
                'type' => strtolower(LimeSoda_Cashpresso_Model_Api_Client::CODE_SIMULATION_SUCCESS),
                'purchaseID' => 'SIM-....b60a'
            ));
        } else {
            $this->norouteAction();
        }
    }
}