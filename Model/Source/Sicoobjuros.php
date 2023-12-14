<?php

/**
 *
 * @category   Cammino
 * @package    Cammino_Payment
 * @author     Cammino Digital
 * @copyright 2023 Cammino Digital
 */

class Cammino_Payment_Model_Source_Sicoobjuros
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array(
            '1' => array(
                'value' => '1',
                'label' => 'Valor por dia'
            ),
            '2' => array(
                'value' => '2',
                'label' => 'Taxa Mensal'
            ),
            '3' => array(
                'value' => '3',
                'label' => 'Isento'
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