<?php
/**
 * 19.02.18
 * LimeSoda - cashpresso
 *
 * Created by Anton Sannikov.
 *
 * @category    Limesoda_Cashpresso
 * @package     cashpresso
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Account.php
 */

class Limesoda_Cashpresso_Model_Api_Account extends Limesoda_Cashpresso_Model_Api_Abstract
{
    const METHOD_TARGET_ACCOUNTS = 'partner/targetAccounts';

    public function getTargetAccounts()
    {
        if ($this->_helper()->checkStatus()) {
            $request = $this->getRequest(self::METHOD_TARGET_ACCOUNTS);

            $request->setMethod(Varien_Http_Client::POST);
            $request->setRawData(Mage::helper('core')->jsonEncode(array(
                'partnerApiKey' => $this->getPartnerApiKey(),
                'verificationHash' => hash('sha512', $this->getSecretKey() . ';' . $this->getPartnerApiKey())
            )), 'application/json');

            try {
                $response = $request->request();

                if ($response->isSuccessful()) {
                    $respond = Mage::helper('core')->jsonDecode($response->getBody());

                    if (is_array($respond)) {
                        if (empty($respond['success'])) {
                            throw new Exception(Mage::helper('ls_cashpresso')->__($respond['error']['description']));
                        }

                        if (empty($respond['targetAccounts'])) {
                            throw new Exception(Mage::helper('ls_cashpresso')->__('Empty target account response.'));
                        }

                        return $respond['targetAccounts'];
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return array();
    }
}