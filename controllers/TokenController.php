<?php
class Cammino_Payment_TokenController extends Mage_Core_Controller_Front_Action {

    public function zaazAction()
    {

        Mage::log('-- Iniciando tokenização ZaaZ Pay --', null, 'payment.log');

        $url = (Mage::getStoreConfig("payment/cammino_payment_zaaz/mode") == 'hml') ? 'https://api-homolog.credpay.com.br/AuthCredPay' : 'https://api.credpay.com.br/AuthCredPay';
        $data = array(
            'User' => Mage::getStoreConfig("payment/cammino_payment_zaaz/user"),
            'Key' => Mage::getStoreConfig("payment/cammino_payment_zaaz/key"),
            'grant_type' => 'password',
        );

        Mage::log('Request autenticação: ' . json_encode($data), null, 'payment.log');

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
            CURLOPT_RETURNTRANSFER => true
        );
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $responseAuth = curl_exec($curl);
        curl_close($curl);
        $responseAuthDecoded = json_decode($responseAuth, true);

        // Handle the response
        Mage::log('Response Autenticação:', null, 'payment.log');
        Mage::log($responseAuth, null, 'payment.log');

        if(!empty($responseAuthDecoded['access_token'])) {

            $url = (Mage::getStoreConfig("payment/cammino_payment_zaaz/mode") == 'hml') ? 'https://api-homolog.credpay.com.br/Tokenizacao/CriarToken' : 'https://api.credpay.com.br/Tokenizacao/CriarToken';
            $data = array(
                "nomeDoCliente" => $this->getRequest()->getPost('nomeDoCliente'),
                "documento" => $this->getRequest()->getPost('documento'),
                "email" => $this->getRequest()->getPost('email'),
                "telefone" => $this->getRequest()->getPost('telefone'),
                "celular" => $this->getRequest()->getPost('celular'),
                "typeCard" => $this->getRequest()->getPost('typeCard'),
                "number" => $this->getRequest()->getPost('number'),
                "expirationMonth" => $this->getRequest()->getPost('expirationMonth'),
                "expirationYear" => $this->getRequest()->getPost('expirationYear'),
                "cvv" => $this->getRequest()->getPost('cvv'),
                "brand" => $this->getRequest()->getPost('brand'),
                "bandeira" => $this->getRequest()->getPost('bandeira'),
                "custoConveniencia" => 0,
                "valorInicial" => Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal(),
                "quantidadeVezesRecorrencia" => 0,
                "dataCobrancaRecorrente" => "",
                "holderName" => $this->getRequest()->getPost('holderName'),
                "holderNameDocumento" => $this->getRequest()->getPost('holderNameDocumento')
            );
            Mage::log('Request tokenização: ' . json_encode($data), null, 'payment.log');
    
            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $responseAuthDecoded['access_token'],
                    'Content-Type: application/x-www-form-urlencoded'
                ),
                CURLOPT_RETURNTRANSFER => true
            );
            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $responseToken = curl_exec($curl);
            curl_close($curl);
    
            Mage::log('Response Tokenização:', null, 'payment.log');
            $responseDecoded = json_decode($responseToken, true);
            $responseDecoded['access_token'] = $responseAuthDecoded['access_token'];
            Mage::log(json_decode($responseToken, true), null, 'payment.log');
            $responseToken['access_token'] = $responseAuthDecoded['access_token'];
            Mage::log($responseToken, null, 'payment.log');
            
            $this->getResponse()->setBody(json_encode($responseDecoded));
        }
        
        return;
    }

    public function ipagAction()
    {

        Mage::log('-- Iniciando tokenização iPag --', null, 'payment.log');

        $baseUrl = (Mage::getStoreConfig("payment/cammino_payment_ipag/mode") == 'production')
            ? 'https://api.ipag.com.br'
            : 'https://sandbox.ipag.com.br';

        $data = array(
            'card' => array(
                'holderName' => $this->getRequest()->getPost('holderName'),
                'number' => preg_replace('/\D/', '', $this->getRequest()->getPost('number')),
                'expiryMonth' => str_pad($this->getRequest()->getPost('expiryMonth'), 2, '0', STR_PAD_LEFT),
                'expiryYear' => $this->getRequest()->getPost('expiryYear'),
                'cvv' => $this->getRequest()->getPost('cvv'),
                'validate' => false
            ),
            'holder' => array(
                'name' => $this->getRequest()->getPost('holderName'),
                'cpfCnpj' => preg_replace('/\D/', '', $this->getRequest()->getPost('cpfCnpj'))
            )
        );

        Mage::log('Request tokenização iPag: ' . json_encode($data), null, 'payment.log');

        $jsonBody = json_encode($data);
        $options = array(
            CURLOPT_URL => $baseUrl . '/service/resources/card_tokens',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonBody),
                'x-api-version: 2',
                'Authorization: Basic ' . base64_encode(
                    Mage::getStoreConfig("payment/cammino_payment_ipag/api_id") . ':' . Mage::getStoreConfig("payment/cammino_payment_ipag/api_key")
                )
            ),
            CURLOPT_RETURNTRANSFER => true
        );
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $responseToken = curl_exec($curl);
        curl_close($curl);

        Mage::log('Response Tokenização iPag: ' . $responseToken, null, 'payment.log');

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setBody($responseToken);

        return;
    }

}