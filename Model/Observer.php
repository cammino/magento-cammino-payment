<?php

class Cammino_Payment_Model_Observer
{

    public function updateSettings(Varien_Event_Observer $observer)
    {

        Mage::log('entrou observer', null, 'payment.log');

        if (!Mage::getStoreConfig("payment/cammino_payment_config/active")) {
            return;
        }

        $request = [
            'store_id' => Mage::getStoreConfig("payment/cammino_payment_config/store_id"),
            'store_name' => Mage::getStoreConfig("payment/cammino_payment_config/store_name"),
            'magento1_api_login' => Mage::getStoreConfig("payment/cammino_payment_config/magento1_api_login"),
            'magento1_api_key' => Mage::getStoreConfig("payment/cammino_payment_config/magento1_api_key"),
        ];
        if (Mage::getStoreConfig("payment/cammino_payment_pagarme/active")) {
            $request['pagarme'] = [
                'api_key' => Mage::getStoreConfig("payment/cammino_payment_pagarme/api_key"),
                'encryption_key' => Mage::getStoreConfig("payment/cammino_payment_pagarme/encryption_key"),
                'mode' => Mage::getStoreConfig("payment/cammino_payment_pagarme/mode")
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_pix/active")) {
            $request['pix'] = [
                'key' => Mage::getStoreConfig("payment/cammino_payment_pix/pix_key")
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_cc/active")) {
            $request['cc'] = [
                'max_installments' => Mage::getStoreConfig("payment/cammino_payment_cc/installments_max"),
                'min_installment_value' => Mage::getStoreConfig("payment/cammino_payment_cc/installments_min"),
                'free_installments' => Mage::getStoreConfig("payment/cammino_payment_cc/installments_free"),
                'installment_tax' => Mage::getStoreConfig("payment/cammino_payment_cc/installments_interest")
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_bol/active")) {
            $request['bol'] = [
                'days_to_expire' => Mage::getStoreConfig("payment/cammino_payment_bol/days_to_expire")
            ];
        }

        Mage::log($request, null, 'payment.log');

        $jsonBody = json_encode($request);

        $url = 'https://payment.cammino.digital/settings';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonBody),
            'apikey: ' . Mage::getStoreConfig("payment/cammino_payment_config/api_key")
        ));
        $response = curl_exec($curl);
        $responseArray = json_decode($response, true);
        curl_close($curl);

        Mage::log('Payment config API response: ' . $response, null, 'payment.log');
        Mage::log('Store ID: ' . $responseArray['store_id'], null, 'payment.log');
        Mage::getModel('core/config')->saveConfig('payment/cammino_payment_config/store_id', $responseArray['store_id']);


    }

}