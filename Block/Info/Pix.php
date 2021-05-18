<?php
class Cammino_Payment_Block_Info_Pix extends Cammino_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cammino_payment/info_pix.phtml');
    }
}