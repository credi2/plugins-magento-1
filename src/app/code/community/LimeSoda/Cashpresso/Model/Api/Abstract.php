<?php
/**
 * 19.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    LimeSoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Abstract.php
 */

abstract class LimeSoda_Cashpresso_Model_Api_Abstract
{
    const CP_ERROR_MULTIPLE_ERRORS = 'MULTIPLE_ERRORS';
    const CP_ERROR_INVALID_INPUT = 'INVALID_INPUT';
    const CP_ERROR_LIMIT_EXCEEDED = 'LIMIT_EXCEEDED';
    const CP_ERROR_INVALID_ZIP = 'INVALID_ZIP';
    const CP_ERROR_UNPARSABLE = 'UNPARSABLE';
    const CP_ERROR_VERIFICATION_FAILED = 'VERIFICATION_FAILED';
    const CP_ERROR_VERIFICATION_TIMEOUT = 'VERIFICATION_TIMEOUT';
    const CP_ERROR_INVALID_STATE = 'INVALID_STATE';
    const CP_ERROR_DUPLICATE_PHONE = 'DUPLICATE_PHONE';
    const CP_ERROR_DUPLICATE_IBAN = 'DUPLICATE_IBAN';
    const CP_ERROR_DUPLICATE_EMAIL = 'DUPLICATE_EMAIL';
    const CP_ERROR_INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    const CP_ERROR_DUPLICATE_CUSTOMER = 'DUPLICATE_CUSTOMER';

    /**
     * @var bool
     */
    protected $_mode = false;

    protected function _helper()
    {
        return Mage::helper('ls_cashpresso');
    }

    /**
     * @param $respond
     * @return bool
     */
    protected function handleRespond($respond)
    {
        if (empty($respond['success'])) {
            $errors = isset($respond['errors']) ? $respond['errors'] : array($respond['error']);

            foreach ($errors as $error) {
                if (!empty($error['type']) && $this->handleError($error['type'])) {
                    $this->getSession()->addWarning(
                        $this->_helper()->__($this->handleError($error['type'])) . ' - ' . $error['description']
                    );
                }
            }

            return false;
        }

        return $respond;
    }

    /**
     * @param $code
     * @return null|string
     */
    public function handleError($code)
    {
        $message = null;

        switch ($code) {
            case self::CP_ERROR_MULTIPLE_ERRORS:
                $message = $this->_helper()->__('Cashpresso: Multiple validation errors - check errors element for details');
                break;
            case self::CP_ERROR_INVALID_INPUT:
                $message = $this->_helper()->__('Cashpresso: Input is invalid or malformed');
                break;
            case self::CP_ERROR_LIMIT_EXCEEDED:
                $message = $this->_helper()->__('Cashpresso: The amount or necessary prepayment is too high');
                break;
            case self::CP_ERROR_INVALID_ZIP:
                $message = $this->_helper()->__('Cashpresso: Zip address validation failed');
                break;
            case self::CP_ERROR_UNPARSABLE:
                $message = $this->_helper()->__('Cashpresso: Input cannot be parsed - format error');
                break;
            case self::CP_ERROR_VERIFICATION_FAILED:
                $message = $this->_helper()->__('Cashpresso: Verification failed (e.g. of TAN code)');
                break;
            case self::CP_ERROR_VERIFICATION_TIMEOUT:
                $message = $this->_helper()->__('Cashpresso: Verification failed due to timeout (e.g. TAN timeout)');
                break;
            case self::CP_ERROR_INVALID_STATE:
                $message = $this->_helper()->__('Cashpresso: Operation is not allowed in current state');
                break;
            case self::CP_ERROR_DUPLICATE_PHONE:
                $message = $this->_helper()->__('Cashpresso: Customer with phone number already exists');
                break;
            case self::CP_ERROR_DUPLICATE_IBAN:
                $message = $this->_helper()->__('Cashpresso: Customer with iban already exists');
                break;
            case self::CP_ERROR_DUPLICATE_EMAIL:
                $message = $this->_helper()->__('Cashpresso: Customer with email already exists');
                break;
            case self::CP_ERROR_INTERNAL_SERVER_ERROR:
                $message = $this->_helper()->__('Cashpresso: Customer with this identity (name, birthdate, etc..) already exists');
                break;
            case self::CP_ERROR_DUPLICATE_CUSTOMER:
                $message = $this->_helper()->__('Cashpresso: Unexpected error - please contact your account manager');
                break;
        }

        return $message;
    }

    /**
     * @return mixed
     */
    public function getPartnerApiKey()
    {
        return Mage::helper('ls_cashpresso')->getAPIKey();
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return Mage::helper('ls_cashpresso')->getSecretKey();
    }

    /**
     * @param bool $test
     * @return $this
     */
    public function setMode($test = false)
    {
        $this->_mode = $test;

        return $this;
    }

    /**
     * @return string
     */
    public function getTestUrl()
    {
        return "https://test.cashpresso.com/rest/backend/ecommerce/v2/";
    }

    /**
     * @return string
     */
    public function getLiveUrl()
    {
        return "https://backend.cashpresso.com/rest/backend/ecommerce/v2/";
    }

    /**
     * @param $method
     * @return Varien_Http_Client
     */
    protected function getRequest($method)
    {
        $client = new Varien_Http_Client(($this->_mode ? $this->getLiveUrl() : $this->getTestUrl()) . $method);
        $client->setMethod(Varien_Http_Client::POST);
        $client->setRawData(Mage::helper('core')->jsonEncode(array(
            'partnerApiKey' => $this->getPartnerApiKey()
        )), 'application/json');

        return $client;
    }

    /**
     * @return Mage_Core_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton((Mage::app()->getStore()->isAdmin() ? 'adminhtml' : 'core') . '/session');
    }
}