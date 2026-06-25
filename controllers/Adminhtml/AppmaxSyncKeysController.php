<?php
class Cammino_Payment_Adminhtml_AppmaxSyncKeysController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $storeId = Mage::getStoreConfig('payment/cammino_payment_config/store_id');
        $apiUrl  = Mage::getStoreConfig('payment/cammino_payment_config/api_url');
        $apiKey  = Mage::getStoreConfig('payment/cammino_payment_config/api_key');

        if (!$storeId) {
            Mage::getSingleton('adminhtml/session')->addError('Store ID não configurado. Salve as configurações gerais do Cammino Payment primeiro.');
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'payment'));
            return;
        }

        $payload = json_encode(array('store_id' => $storeId));

        $curl = curl_init(rtrim($apiUrl, '/') . '/app/appmax/credentials');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'apikey: ' . $apiKey,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        Mage::log('[Appmax] sync keys response: ' . $response, null, 'payment.log');

        $data = json_decode($response, true);

        if (!empty($data['client_id'])) {
            $config = Mage::getModel('core/config');
            $config->saveConfig('payment/cammino_payment_appmax/client_id', $data['client_id']);
            if (!empty($data['client_secret'])) {
                $config->saveConfig('payment/cammino_payment_appmax/client_secret', $data['client_secret']);
            }
            if (!empty($data['external_id'])) {
                $config->saveConfig('payment/cammino_payment_appmax/external_id', $data['external_id']);
            }
            Mage::app()->getCacheInstance()->cleanType('config');
            Mage::getSingleton('adminhtml/session')->addSuccess('Chaves AppMax atualizadas com sucesso.');
        } else {
            $message = !empty($data['message']) ? $data['message'] : $response;
            Mage::getSingleton('adminhtml/session')->addError('Erro ao buscar chaves AppMax: ' . $message);
        }

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'payment'));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config');
    }
}
