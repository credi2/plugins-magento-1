<?php
/**
 * 01.03.18
 * LimeSoda - dockerized-magento (Cashpresso)
 *
 * Created by Anton Sannikov.
 *
 * @category    Lime_Soda
 * @package     dockerized-magento (Cashpresso)
 * @copyright   Copyright (c) 2018 LimeSoda. (http://www.limesoda.com)
 *
 * @file Request.php
 */

class LimeSoda_Cashpresso_Helper_Request extends Mage_Core_Helper_Abstract
{
    /**
     * @return mixed
     */
    public function getCheckoutUrl()
    {
        $configUrl = Mage::getStoreConfig(LimeSoda_Cashpresso_Helper_Data::XML_PARTNER_CHECKOUT_URL);

        $urlObject = new stdClass();
        $urlObject->url = $this->_getUrl($configUrl?:'checkout/onepage', array('_secure' => true));

        Mage::dispatchEvent('cashpresso_js_c2checkout_url', array('url' => $urlObject));

        return $urlObject->url;
    }

    /**
     * @return bool
     */
    public function isProductPage()
    {
        return Mage::registry('current_product') ? true : false;
    }

    /**
     * @param $response
     * @param $key
     * @return bool
     */
    public function hashCheck($response, $key)
    {
        $remoteHash = isset($response['verificationHash']) ? $response['verificationHash'] : '';
        $localHash = hash('sha512', $key . ';' . $response['status'] . ';' . $response['referenceId'] . ';' . $response['usage']);

        return $remoteHash == $localHash;
    }
}