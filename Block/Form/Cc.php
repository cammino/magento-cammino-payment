<?php
class Cammino_Payment_Block_Form_Cc extends Cammino_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cammino_payment/form_cc.phtml');
    }
}