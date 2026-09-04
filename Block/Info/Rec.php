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
     * @return float
     */
    public function getRecurrenceAmount()
    {
        $order = $this->getInfo()->getOrder();
        $recurrences = $this->getNumberOfRecurrences();
        return ($order && $recurrences) ? ($order->getGrandTotal() / $recurrences) : 0;
    }
}
