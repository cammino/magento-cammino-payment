<?php
class Cammino_Payment_Block_Adminhtml_Appmax_SyncKeys extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $syncUrl = Mage::helper('adminhtml')->getUrl('adminhtml/appmaxSyncKeys/index');

        $html  = '<button type="button" id="appmax_sync_keys_btn" class="scalable">';
        $html .= '<span><span><span>Atualizar Chaves AppMax</span></span></span>';
        $html .= '</button>';
        $html .= '<p id="appmax_sync_keys_msg" style="margin-top:6px;color:#666"></p>';
        $html .= '<script type="text/javascript">';
        $html .= 'document.getElementById("appmax_sync_keys_btn").onclick = function() {';
        $html .= '    document.getElementById("appmax_sync_keys_msg").innerHTML = "Aguarde...";';
        $html .= '    window.location.href = "' . $syncUrl . '";';
        $html .= '};';
        $html .= '</script>';

        return $html;
    }
}
