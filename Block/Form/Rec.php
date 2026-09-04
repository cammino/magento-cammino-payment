<?php
class Cammino_Payment_Block_Form_Rec extends Cammino_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cammino_payment/form_rec.phtml');
    }

    /**
     * Return months to populate dropdown in checkout field
     * @return array|mixed|null
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Mês');
            for ($i=1; $i <= 12; $i++) {
                $months[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Return years to populate dropdown in checkout field
     * @return array|mixed|null
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = Mage::getSingleton('payment/config')->getYears();
            $years = array(0=>$this->__('Ano'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
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
        $total = Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal();
        $recurrences = $this->getNumberOfRecurrences();
        return $recurrences ? ($total / $recurrences) : $total;
    }
}
