<?php
class Cammino_Payment_Block_Info_Bol extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cammino_payment/info_bol.phtml');
    }
}