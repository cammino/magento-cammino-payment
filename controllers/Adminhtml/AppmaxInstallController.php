<?php
class Cammino_Payment_Adminhtml_AppmaxInstallController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $mode    = $this->getRequest()->getParam('mode', 'sandbox');
        $storeId = Mage::getStoreConfig('payment/cammino_payment_config/store_id');
        $apiUrl  = Mage::getStoreConfig('payment/cammino_payment_config/api_url');
        $apiKey  = Mage::getStoreConfig('payment/cammino_payment_config/api_key');

        if (!$storeId) {
            Mage::getSingleton('adminhtml/session')->addError('Store ID não configurado. Salve as configurações gerais do Cammino Payment primeiro.');
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'payment'));
            return;
        }

        $payload = json_encode(array('store_id' => $storeId, 'mode' => $mode));

        $curl = curl_init(rtrim($apiUrl, '/') . '/app/appmax/install');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'apikey: ' . $apiKey,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        Mage::log('[Appmax] install response: ' . $response, null, 'payment.log');

        $data = json_decode($response, true);
        if (!empty($data['redirect_url'])) {
            $this->_redirectUrl($data['redirect_url']);
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Erro ao iniciar instalação AppMax: ' . $response);
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'payment'));
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config');
    }
}
