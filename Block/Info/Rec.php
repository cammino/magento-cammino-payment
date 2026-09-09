<?php
class Cammino_Payment_Block_Info_Rec extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cammino_payment/info_rec.phtml');
    }

    /**
     * @return int
     */
    public function getNumberOfRecurrences()
    {
        return (int) Mage::getStoreConfig('payment/cammino_payment_rec/number_of_recurrences') ?: 1;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        $saved = $this->getInfo()->getAdditionalInformation('cammino_payment_rec_summary');
        if ($saved) {
            return $saved;
        }

        $order = $this->getInfo()->getOrder();
        $cycles = $this->getNumberOfRecurrences();
        if (!$order || !$cycles) {
            return '';
        }
        return $cycles . ' cobranças de ' . Mage::helper('core')->currency($order->getGrandTotal() / $cycles, true, false);
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getInfo()->getCamminoPaymentTransactionId();
    }
}
