<?php
class Cammino_Payment_Model_Rec extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'cammino_payment_rec';
    protected $_isGateway                   = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = true;
    protected $_canUseInternal              = true;
    protected $_formBlockType = 'cammino_payment/form_rec';
    protected $_infoBlockType = 'cammino_payment/info_rec';
    protected $_canOrder  = true;

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (!parent::isAvailable($quote)) {
            return false;
        }

        if (!$quote) {
            return false;
        }

        $allowedCategoryIds = array_filter(explode(',', (string) Mage::getStoreConfig('payment/cammino_payment_rec/category_ids')));
        if (!$allowedCategoryIds) {
            return false;
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $productCategoryIds = $product ? $product->getCategoryIds() : array();
            if (!array_intersect($allowedCategoryIds, $productCategoryIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function getNumberOfRecurrences()
    {
        return (int) Mage::getStoreConfig('payment/cammino_payment_rec/number_of_recurrences') ?: 1;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();

        $this->_placeOrder($payment, $order->getBaseTotalDue());

        return $this;
    }

    /**
     * @param mixed $data
     * @return $this|Mage_Payment_Model_Info
     * @throws Mage_Core_Exception
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $additional = array();

        if ($data->getCamminoPaymentRecCcOwner()) {
            $additional['cammino_payment_rec_cc_owner'] = $data->getCamminoPaymentRecCcOwner();
        }

        if ($data->getCamminoPaymentRecCcNumber()) {
            $additional['cammino_payment_rec_cc_number'] = $data->getCamminoPaymentRecCcNumber();
        }

        if ($data->getCamminoPaymentRecExpiration()) {
            $additional['cammino_payment_rec_expiration'] = $data->getCamminoPaymentRecExpiration();
        }

        if ($data->getCamminoPaymentRecExpirationYr()) {
            $additional['cammino_payment_rec_expiration_yr'] = $data->getCamminoPaymentRecExpirationYr();
        }

        if ($data->getCamminoPaymentRecCcCid()) {
            $additional['cammino_payment_rec_cc_cid'] = $data->getCamminoPaymentRecCcCid();
        }

        if ($data->getCamminoPaymentRecCpf()) {
            $additional['cammino_payment_rec_cpf'] = $data->getCamminoPaymentRecCpf();
        }

        if ($data->getCamminoPaymentRecIpagCardToken()) {
            $additional['cammino_payment_rec_ipag_card_token'] = $data->getCamminoPaymentRecIpagCardToken();
        }

        if ($additional) {
            $info->setAdditionalInformation($additional);
        }
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {

        try {

            Mage::log(' --- Place Order - Cammino Payment - Recorrência no Cartão ---', null, 'payment.log');

            $order           = $payment->getOrder();
            $items           = $order->getAllItems();
            $customer        = $order->getCustomer();
            $shippingAddress = $order->getShippingAddress();
            $billingAddress  = $order->getBillingAddress();

            $gateway = Mage::getStoreConfig("payment/cammino_payment_rec/gateway");
            if (empty(Mage::getStoreConfig("payment/cammino_payment_" . $gateway . "/active"))) {
                throw new Exception('Sem gateway de pagamento configurado ou gateway desativado.');
            }

            $requestJson = [
                "type" => "authorization",
                "store_id" => Mage::getStoreConfig("payment/cammino_payment_config/store_id"),
                "order_id" => $order->getIncrementId(),
                "status" => "pending",
                "amount" => $order->getGrandTotal(),
                "shipping_amount" => $order->getShippingAmount(),
                "method" => $gateway . "_rec",
                "session" => "",
                "mode" => "authorize_capture",
                "customer" => [
                    "id" => $customer->getId(),
                    "name" => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    "email" => $customer->getEmail(),
                    "type" => (strlen($customer->getTaxvat()) > 11) ? 'CNPJ' : 'CPF',
                    "document" => $customer->getTaxvat(),
                    "phone" => $billingAddress->getTelephone(),
                    "birthday" => $customer->getDob(),
                    "country" => "Brasil",
                    "ip" => Mage::helper('core/http')->getRemoteAddr()
                ],
                "billing" => [
                    "address1" => (!empty($billingAddress->getStreet()[0])) ? $billingAddress->getStreet()[0] : '',
                    "address2" => (!empty($billingAddress->getStreet()[1])) ? $billingAddress->getStreet()[1] : '',
                    "address3" => (!empty($billingAddress->getStreet()[2])) ? $billingAddress->getStreet()[2] : '',
                    "address4" => (!empty($billingAddress->getStreet()[3])) ? $billingAddress->getStreet()[3] : '',
                    "city" => $billingAddress->getCity(),
                    "region" => self::getRegionSigla($billingAddress->getRegion()),
                    "country" => "Brasil",
                    "zipcode" => str_replace('-', '', $billingAddress->getPostcode()),
                ],
                "shipping" => [
                    "address1" => (!empty($shippingAddress->getStreet()[0])) ? $shippingAddress->getStreet()[0] : '',
                    "address2" => (!empty($shippingAddress->getStreet()[1])) ? $shippingAddress->getStreet()[1] : '',
                    "address3" => (!empty($shippingAddress->getStreet()[2])) ? $shippingAddress->getStreet()[2] : '',
                    "address4" => (!empty($shippingAddress->getStreet()[3])) ? $shippingAddress->getStreet()[3] : '',
                    "city" => $shippingAddress->getCity(),
                    "region" => self::getRegionSigla($shippingAddress->getRegion()),
                    "country" => "Brasil",
                    "zipcode" => str_replace('-', '', $shippingAddress->getPostcode()),
                ],
                "items" => [],
                "ip" => ""
            ];

            $requestJson['cc_owner'] = $payment->getAdditionalInformation('cammino_payment_rec_cc_owner');
            $requestJson['cc_owner_document'] = $payment->getAdditionalInformation('cammino_payment_rec_cpf');
            if (!empty($payment->getAdditionalInformation('cammino_payment_rec_ipag_card_token'))) {
                $requestJson['cc_token'] = $payment->getAdditionalInformation('cammino_payment_rec_ipag_card_token');
            }
            $requestJson["cc_brand"] = self::getCcBrand($payment->getAdditionalInformation('cammino_payment_rec_cc_number'));
            $requestJson['cc_number'] = self::encrypt($payment->getAdditionalInformation('cammino_payment_rec_cc_number'));
            $requestJson['cc_cvv'] = self::encrypt($payment->getAdditionalInformation('cammino_payment_rec_cc_cid'));
            $requestJson['cc_expiration'] = self::encrypt($payment->getAdditionalInformation('cammino_payment_rec_expiration') . '/' . $payment->getAdditionalInformation('cammino_payment_rec_expiration_yr'));
            $requestJson['cc_expiration_month'] = self::encrypt($payment->getAdditionalInformation('cammino_payment_rec_expiration'));
            $requestJson['cc_expiration_year'] = self::encrypt($payment->getAdditionalInformation('cammino_payment_rec_expiration_yr'));

            foreach ($items as $item) {
                $requestJson["items"][] = [
                    "sku" => $item->getSku(),
                    "name" => $item->getName(),
                    "price" => $item->getPrice(),
                    "qty" => $item->getQtyOrdered()
                ];
            }

            $jsonBody = json_encode($requestJson);

            Mage::log('REQUEST::: ' . $jsonBody, null, 'payment.log');

            $url = Mage::getStoreConfig("payment/cammino_payment_config/api_url") . '/transactions';
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

            Mage::log('RESPONSE::: ' . $response, null, 'payment.log');

            if ($responseArray['status'] == 'error') {
                Mage::throwException('Erro no pagamento: ' . $responseArray['message']);
                throw new Exception('Erro no pagamento: ' . $responseArray['message']);
            }

            $payment
                ->setCamminoPaymentTransactionId($responseArray['transaction_id'])
                ->setCamminoPaymentDigitableLine($responseArray['digitable_line'])
                ->setCamminoPaymentUrl($responseArray['url'])
                ->save();

            return $this;

        } catch (Mage_Core_Exception $e) {
            Mage::throwException($e->getMessage());
            Mage::log('erro: ' . $e->getMessage(), null, 'payment.log');
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
            Mage::log('erro: ' . $e->getMessage(), null, 'payment.log');
        }

    }

    private function encrypt($plaintext) {
        $password       = 'lbwyBzfgzUIvXZFShJuikaWvLJhIVq36';
        $iv_size        = openssl_cipher_iv_length('aes-256-cbc');
        $iv             = openssl_random_pseudo_bytes($iv_size);
        $ciphertext     = openssl_encrypt($plaintext, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv);
        $ciphertext_hex = bin2hex($ciphertext);
        $iv_hex         = bin2hex($iv);
        return "$iv_hex:$ciphertext_hex";
    }

    private function getCcBrand($number) {
      switch ($number[0]) {
          case 3:
              if ($number[1] == 6 || $number[1] == 8) {
                $brand = "Diners";
              } else if ($number[1] == 4 || $number[1] == 7) {
                $brand = "American Express";
              } else if ($number[1] == 5) {
                $brand = "JCB";
              }
              break;
          case 4:
              $brand = "Visa";
              break;
          case 5:
              if ($number[1] < 6) {
                $brand = "Master";
              } else {
                $brand = "Elo";
              }
              break;
          case 6:
              $brand = "Discover";
              break;
      }
      return $brand;
    }

    private function getRegionSigla($needle)
    {
        $regions = [
            ["Sigla" => "AC", "Nome" => "Acre"],
            ["Sigla" => "AL", "Nome" => "Alagoas"],
            ["Sigla" => "AP", "Nome" => "Amapá"],
            ["Sigla" => "AM", "Nome" => "Amazonas"],
            ["Sigla" => "BA", "Nome" => "Bahia"],
            ["Sigla" => "CE", "Nome" => "Ceará"],
            ["Sigla" => "DF", "Nome" => "Distrito Federal"],
            ["Sigla" => "ES", "Nome" => "Espírito Santo"],
            ["Sigla" => "GO", "Nome" => "Goiás"],
            ["Sigla" => "MA", "Nome" => "Maranhão"],
            ["Sigla" => "MT", "Nome" => "Mato Grosso"],
            ["Sigla" => "MS", "Nome" => "Mato Grosso do Sul"],
            ["Sigla" => "MG", "Nome" => "Minas Gerais"],
            ["Sigla" => "PA", "Nome" => "Pará"],
            ["Sigla" => "PB", "Nome" => "Paraíba"],
            ["Sigla" => "PR", "Nome" => "Paraná"],
            ["Sigla" => "PE", "Nome" => "Pernambuco"],
            ["Sigla" => "PI", "Nome" => "Piauí"],
            ["Sigla" => "RJ", "Nome" => "Rio de Janeiro"],
            ["Sigla" => "RN", "Nome" => "Rio Grande do Norte"],
            ["Sigla" => "RS", "Nome" => "Rio Grande do Sul"],
            ["Sigla" => "RO", "Nome" => "Rondônia"],
            ["Sigla" => "RR", "Nome" => "Roraima"],
            ["Sigla" => "SC", "Nome" => "Santa Catarina"],
            ["Sigla" => "SP", "Nome" => "São Paulo"],
            ["Sigla" => "SE", "Nome" => "Sergipe"],
            ["Sigla" => "TO", "Nome" => "Tocantins"]
        ];
        $sigla = '';
        foreach ($regions as $region) {
            if ($region['Nome'] == $needle) {
                $sigla = $region['Sigla'];
            }
        }
        return $sigla;
    }
}
