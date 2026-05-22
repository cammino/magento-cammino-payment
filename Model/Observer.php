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
            'store_version' => '1',
            'base_url' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
        ];
        if (Mage::getStoreConfig("payment/cammino_payment_pagarme/active")) {
            $request['pagarme'] = [
                'api_key' => Mage::getStoreConfig("payment/cammino_payment_pagarme/api_key"),
                'encryption_key' => Mage::getStoreConfig("payment/cammino_payment_pagarme/encryption_key"),
                'mode' => (Mage::getStoreConfig("payment/cammino_payment_pagarme/mode") == 'production') ? 'production' : 'homolog'
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_zaaz/active")) {
            $request['zaaz'] = [
                'loja_id' => Mage::getStoreConfig("payment/cammino_payment_zaaz/loja_id"),
                'user' => Mage::getStoreConfig("payment/cammino_payment_zaaz/user"),
                'key' => Mage::getStoreConfig("payment/cammino_payment_zaaz/key"),
                'mode' => (Mage::getStoreConfig("payment/cammino_payment_zaaz/mode") == 'production') ? 'prod' : 'hml'
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_cielo/active")) {
            $request['cielo'] = [
                'merchant_id' => Mage::getStoreConfig("payment/cammino_payment_cielo/merchant_id"),
                'merchant_key' => Mage::getStoreConfig("payment/cammino_payment_cielo/merchant_key"),
                'provider' => Mage::getStoreConfig("payment/cammino_payment_cielo/provider"),
                'mode' => (Mage::getStoreConfig("payment/cammino_payment_cielo/mode") == 'production') ? 'production' : 'test'
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_erede/active")) {
            $request['erede'] = [
                'token' => Mage::getStoreConfig("payment/cammino_payment_erede/token"),
                'pv' => Mage::getStoreConfig("payment/cammino_payment_erede/pv"),
                'mode' => (Mage::getStoreConfig("payment/cammino_payment_erede/mode") == 'production') ? 'production' : 'sandbox'
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_sicoob/active")) {
            $request['sicoob'] = [
                'client_id' => Mage::getStoreConfig("payment/cammino_payment_sicoob/client_id"),
                'secret_id' => Mage::getStoreConfig("payment/cammino_payment_sicoob/secret_id"),
                'conta_corrente' => Mage::getStoreConfig("payment/cammino_payment_sicoob/conta_corrente"),
                'numero_contrato' => Mage::getStoreConfig("payment/cammino_payment_sicoob/numero_contrato"),
                'mode' => (Mage::getStoreConfig("payment/cammino_payment_sicoob/mode") == 'production') ? 'produto' : 'sandbox'
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
        if (Mage::getStoreConfig("payment/cammino_payment_appmax/active")) {
            $request['appmax'] = [
                'access_token' => Mage::getStoreConfig("payment/cammino_payment_appmax/access_token"),
                'mode' => (Mage::getStoreConfig("payment/cammino_payment_appmax/mode") == 'production') ? 'production' : 'sandbox'
            ];
        }

        Mage::log($request, null, 'payment.log');

        $jsonBody = json_encode($request);

        $url = Mage::getStoreConfig("payment/cammino_payment_config/api_url") . '/settings';
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

    public function clearSensitivePaymentData(Varien_Event_Observer $observer)
    {
        Mage::log('Limpando informações sensíveis do additional information...', null, 'payment.log');
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        if (!$payment) {
            return;
        }

        $additional = $payment->getAdditionalInformation();

        if (!is_array($additional)) {
            return;
        }

        $ccLast4 = null;

        if (!empty($additional['cammino_payment_cc_cc_number'])) {
            $ccLast4 = substr($additional['cammino_payment_cc_cc_number'], -4);
        }

        // Remove sensitive fields
        $payment->setAdditionalInformation('cammino_payment_cc_cc_number', '**** **** **** ' . $ccLast4);
        $payment->setAdditionalInformation('cammino_payment_cc_cc_cid', null);
        $payment->setAdditionalInformation('cammino_payment_cc_expiration', null);
        $payment->setAdditionalInformation('cammino_payment_cc_expiration_yr', null);

        try {
            $payment->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'payment.log');
        }
    }

    public function clearQuotePaymentSensitiveData(Varien_Event_Observer $observer)
    {
        Mage::log('Limpando dados sensíveis do QUOTE após falha...', null, 'payment.log');

        $quote = $observer->getEvent()->getQuote();

        if (!$quote) {
            return;
        }

        $payment = $quote->getPayment();

        if (!$payment) {
            return;
        }

        if ($payment->getMethod() !== 'cammino_payment_cc') {
            return;
        }

        $additional = $payment->getAdditionalInformation();

        if (!is_array($additional)) {
            return;
        }

        $ccLast4 = null;

        if (!empty($additional['cammino_payment_cc_cc_number'])) {
            $ccLast4 = substr($additional['cammino_payment_cc_cc_number'], -4);
        }

        $payment->setAdditionalInformation('cammino_payment_cc_cc_cid', '');
        $payment->setAdditionalInformation('cammino_payment_cc_expiration', '');
        $payment->setAdditionalInformation('cammino_payment_cc_expiration_yr', '');

        try {
            $payment->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'payment.log');
        }
    }

}