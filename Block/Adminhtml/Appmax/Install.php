<?php
class Cammino_Payment_Block_Adminhtml_Appmax_Install extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $installUrl = Mage::helper('adminhtml')->getUrl('adminhtml/appmaxInstall/index');

        $html  = '<button type="button" id="appmax_install_btn" class="scalable">';
        $html .= '<span><span><span>Instalar / Reinstalar AppMax</span></span></span>';
        $html .= '</button>';
        $html .= '<p id="appmax_install_msg" style="margin-top:6px;color:#666"></p>';
        $html .= '<script type="text/javascript">';
        $html .= 'document.getElementById("appmax_install_btn").onclick = function() {';
        $html .= '    var modeEl = document.getElementById("payment_cammino_payment_appmax_mode");';
        $html .= '    var mode = modeEl ? modeEl.value : "sandbox";';
        $html .= '    document.getElementById("appmax_install_msg").innerHTML = "Aguarde...";';
        $html .= '    window.location.href = "' . $installUrl . 'mode/" + mode + "/";';
        $html .= '};';
        $html .= '</script>';

        return $html;
    }
}
