<?php


class LimeSoda_Cashpresso_Model_Api_Account extends LimeSoda_Cashpresso_Model_Api_Abstract
{
    const METHOD_TARGET_ACCOUNTS = 'partner/targetAccounts';

    public function getTargetAccounts($showError = false)
    {
        if ($this->_helper()->isModuleEnabled() && (Mage::app()->getStore()->isAdmin() ? $this->_helper()->getAPIKey() : $this->_helper()->checkStatus(false))) {

            $partnerInfo = Mage::helper('ls_cashpresso')->generatePartnerInfo();

            if (!empty($partnerInfo['success'])) {
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
                            $respond = $this->handleRespond($respond, $showError);

                            if (!empty($respond['targetAccounts'])) {
                                return $respond['targetAccounts'];
                            }
                        }
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return array();
    }

    public function checkRequest()
    {
        if ($this->_helper()->isModuleEnabled() && (Mage::app()->getStore()->isAdmin() ? $this->_helper()->getAPIKey() : $this->_helper()->checkStatus(false))) {
            $partnerInfo = Mage::helper('ls_cashpresso')->generatePartnerInfo();

            if (!empty($partnerInfo['success'])) {
                $request = $this->getRequest(self::METHOD_TARGET_ACCOUNTS);

                $request->setMethod(Varien_Http_Client::POST);
                $request->setRawData(Mage::helper('core')->jsonEncode(array(
                    'partnerApiKey' => $this->getPartnerApiKey(),
                    'verificationHash' => hash('sha512', $this->getSecretKey() . ';' . $this->getPartnerApiKey())
                )), 'application/json');

                $response = $request->request();

                if ($response->isSuccessful()) {
                    $respond = Mage::helper('core')->jsonDecode($response->getBody());

                    if (!empty($respond['error']['type']) && $respond['error']['type'] == 'INVALID_STATE') {
                        return true;
                    }

                    $respond = $this->handleRespond($respond, false);

                    if (!$respond) {
                        throw new Exception('Cashpresso: Api key or secret key are invalid.');
                    }
                }
            }
        }
    }
}