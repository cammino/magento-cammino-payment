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
        if (Mage::getStoreConfig("payment/cammino_payment_ipag/active")) {
            $request['ipag'] = [
                'api_id' => Mage::getStoreConfig("payment/cammino_payment_ipag/api_id"),
                'api_key' => Mage::getStoreConfig("payment/cammino_payment_ipag/api_key"),
                'mode' => (Mage::getStoreConfig("payment/cammino_payment_ipag/mode") == 'production') ? 'production' : 'sandbox'
            ];
        }
        if (Mage::getStoreConfig("payment/cammino_payment_rec/active")) {
            $request['rec'] = [
                'gateway' => Mage::getStoreConfig("payment/cammino_payment_rec/gateway"),
                'number_of_recurrences' => Mage::getStoreConfig("payment/cammino_payment_rec/number_of_recurrences"),
                'interval' => Mage::getStoreConfig("payment/cammino_payment_rec/interval")
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
        // Appmax credentials are set via the install flow (not stored in Magento config).
        // Sending them here would overwrite credentials saved by the hub with empty values.

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
        if (is_array($responseArray) && !empty($responseArray['store_id']) && (!isset($responseArray['status']) || $responseArray['status'] !== 'error')) {
            Mage::log('Store ID: ' . $responseArray['store_id'], null, 'payment.log');
            Mage::getModel('core/config')->saveConfig('payment/cammino_payment_config/store_id', $responseArray['store_id']);
        } else {
            Mage::log('Payment config API retornou erro ou sem store_id, mantendo o store_id atual sem alteração.', null, 'payment.log');
        }


    }

    /**
     * Quando um pedido é cancelado no Magento (antes de faturar), avisa a
     * API de pagamento pra ela pedir o estorno na Appmax também - o
     * cartão já pode ter sido cobrado na hora do checkout, mesmo o pedido
     * nunca tendo sido faturado no Magento.
     */
    public function notifyGatewayCancel(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        if (!$payment || strpos((string) $payment->getMethod(), 'cammino_payment_') !== 0) {
            return;
        }

        $gateway = Mage::getStoreConfig("payment/cammino_payment_cc/gateway");
        if ($gateway != 'appmax') {
            return;
        }

        $requestJson = [
            "store_id" => Mage::getStoreConfig("payment/cammino_payment_config/store_id"),
            "order_id" => $order->getIncrementId(),
        ];

        $jsonBody = json_encode($requestJson);
        Mage::log('REQUEST CANCEL::: ' . $jsonBody, null, 'payment.log');

        $url = Mage::getStoreConfig("payment/cammino_payment_config/api_url") . '/transactions/cancel';
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
        curl_close($curl);

        Mage::log('RESPONSE CANCEL::: ' . $response, null, 'payment.log');
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