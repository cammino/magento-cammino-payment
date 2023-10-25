<?php
class Cammino_Payment_Model_Pix extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'cammino_payment_pix';
    protected $_isGateway                   = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = true;
    protected $_canUseInternal              = true;
    protected $_formBlockType = 'cammino_payment/form_pix';
    protected $_infoBlockType = 'cammino_payment/info_pix';
    protected $_canOrder  = true;

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
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {

        try {

            Mage::log(' --- Place Order - Cammino Payment - PIX ---', null, 'payment.log');

            $order           = $payment->getOrder();
            $items           = $order->getAllItems();
            $customer        = $order->getCustomer();
            $shippingAddress = $order->getShippingAddress();
            $billingAddress  = $order->getBillingAddress();
            

            $gateway = Mage::getStoreConfig("payment/cammino_payment_pix/gateway");
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
                "method" => $gateway . "_pix",
                "session" => "",
                "customer" => [
                    "id" => $customer->getId(),
                    "name" => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    "email" => $customer->getEmail(),
                    "type" => (strlen($customer->getTaxvat()) > 11) ? 'CNPJ' : 'CPF',
                    "document" => $customer->getTaxvat(),
                    "phone" => $billingAddress->getTelephone(),
                    "birthday" => $customer->getDob(),
                    "country" => "Brasil"
                ],
                "billing" => [
                    "address1" => (!empty($billingAddress->getStreet()[0])) ? $billingAddress->getStreet()[0] : '', //rua
                    "address2" => (!empty($billingAddress->getStreet()[1])) ? $billingAddress->getStreet()[1] : '', //numero
                    "address3" => (!empty($billingAddress->getStreet()[2])) ? $billingAddress->getStreet()[2] : '', //complemento
                    "address4" => (!empty($billingAddress->getStreet()[3])) ? $billingAddress->getStreet()[3] : '', //bairro
                    "city" => $billingAddress->getCity(),
                    "region" => self::getRegionSigla($billingAddress->getRegion()),
                    "country" => "Brasil",
                    "zipcode" => $billingAddress->getPostcode()
                ],
                "shipping" => [
                    "address1" => (!empty($shippingAddress->getStreet()[0])) ? $shippingAddress->getStreet()[0] : '', //rua
                    "address2" => (!empty($shippingAddress->getStreet()[1])) ? $shippingAddress->getStreet()[1] : '', //numero
                    "address3" => (!empty($shippingAddress->getStreet()[2])) ? $shippingAddress->getStreet()[2] : '', //complemento
                    "address4" => (!empty($shippingAddress->getStreet()[3])) ? $shippingAddress->getStreet()[3] : '', //bairro
                    "city" => $shippingAddress->getCity(),
                    "region" => self::getRegionSigla($shippingAddress->getRegion()),
                    "country" => "Brasil",
                    "zipcode" => $shippingAddress->getPostcode()
                ],
                "items" => [],
                "ip" => ""
            ];
            
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
