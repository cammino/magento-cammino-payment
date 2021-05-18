<?php
class Cammino_Payment_Block_Form_Bol extends Cammino_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cammino_payment/form_bol.phtml');
    }
}