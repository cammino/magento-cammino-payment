<?php
class Cammino_Pixpayment_Model_Pix extends Cammino_Pixpayment_Model_Abstract
{
    protected $_code = 'cammino_payment_pix';
    protected $_formBlockType = 'cammino_payment/form_pix';
    protected $_infoBlockType = 'cammino_payment/info_pix';

    protected $_isGateway = true;
    protected $_canUseForMultishipping = true;
    protected $_isInitializeNeeded = true;
    protected $_canManageRecurringProfiles = false;

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();

        $this->_place($payment, $order->getBaseTotalDue());

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function _place(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        return $this;
    }

}