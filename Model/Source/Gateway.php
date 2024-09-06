<?php

/**
 *
 * @category   Cammino
 * @package    Cammino_Payment
 * @author     Cammino Digital
 * @copyright 2023 Cammino Digital
 */

class Cammino_Payment_Model_Source_Gateway
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array(
            'pagarme' => array(
                'value' => 'pagarme',
                'label' => 'Pagar.me'
            ),
            'pagseguro' => array(
                'value' => 'pagseguro',
                'label' => 'Pagseguro'
            ),
            'cielo' => array(
                'value' => 'cielo',
                'label' => 'Cielo'
            ),
            'yapay' => array(
                'value' => 'yapay',
                'label' => 'Yapay'
            ),
            'tuna' => array(
                'value' => 'tuna',
                'label' => 'Tuna'
            ),
            'zaaz' => array(
                'value' => 'zaaz',
                'label' => 'ZaaZ'
            ),
            'sicoob' => array(
                'value' => 'sicoob',
                'label' => 'Sicoob'
            ),
            'erede' => array(
                'value' => 'erede',
                'label' => 'eRede'
            ),
        );

        return $options;
    }

    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        $arr  = array(array('' => '-'));
        $optionArray = $this->toOptionArray();
        foreach($optionArray as $option){
            $arr[$option['value']] = $option['label'];
        }
    }
}
