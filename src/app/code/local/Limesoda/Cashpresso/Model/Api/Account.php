<?php
/**
 * 19.02.18
 * LimeSoda - dockerized-magento
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Account.php
 */

class Limesoda_Cashpresso_Model_Api_Account extends Limesoda_Cashpresso_Model_Api_Abstract
{
    const METHOD_TARGET_ACCOUNTS = 'partner/targetAccounts';

    public function getTargetAccounts()
    {
        $request = $this->getRequest(self::METHOD_TARGET_ACCOUNTS);

        $request->setMethod(Varien_Http_Client::POST);
        $request->setRawData(json_encode([
            'partnerApiKey' => $this->getPartnerApiKey(),
            'verificationHash' =>  hash('sha512', Mage::helper('core')->decrypt($this->getSecretKey()) . ';' . $this->getPartnerApiKey())
        ]), 'application/json');

        try {
            $response = $request->request();
            
            if ($response->isSuccessful()) {
                $respond = Mage::helper('core')->jsonDecode($response->getBody());

                if (is_array($respond)){
                    if (empty($respond['success'])){
                        throw new Exception(Mage::helper('ls_cashpresso')->__($respond['error']['description']));
                    }

                    return $respond;
                }
            }
        } catch (Exception $e) {
            echo '<pre><br/>';
            var_dump($e->getMessage());
            die();
        }
        die();

        /*
      {
        "targetAccountId" : String (optional),
        "verificationHash" : String,
        "validUntil" : ISO formatted timestamp (yyyy-MM-dd'T'HH:mm:ss.SSSZ, e.g. "2000-10-31T01:30:00.000-05:00"),
        "bankUsage" : String,
        "interestFreeDaysMerchant" : integer,
        "callbackUrl" : String (optional),
        "description" : String (Max-Length: 511, optional),
        "invoiceAddress" : Address (optional),
        "deliveryAddress" : Address (optional),
        "language" : String (optional, 2-letter code e.g. "de"),
        "merchantCustomerId" : String (optional),
        "basket" : [ BasketItem ] (optional)
      }
      The Address element:
      {
        "country" : String (2 letter iso code e.g. "DE"),
        "zip" : String,
        "city" : String,
        "street" : String,
        "housenumber" : String,
      }
      The BaketItem element:
      {
        "description" : String,
        "amount" : number,
        "times" : integer
      }*/
    }

}