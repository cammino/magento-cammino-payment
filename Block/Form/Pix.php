<?php
class Cammino_Payment_Block_Form_Pix extends Cammino_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cammino_payment/form_pix.phtml');
    }
}