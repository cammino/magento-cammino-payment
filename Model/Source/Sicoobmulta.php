<?php

/**
 *
 * @category   Cammino
 * @package    Cammino_Payment
 * @author     Cammino Digital
 * @copyright 2023 Cammino Digital
 */

class Cammino_Payment_Model_Source_Sicoobmulta
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array(
            '0' => array(
                'value' => '0',
                'label' => 'Isento'
            ),
            '1' => array(
                'value' => '1',
                'label' => 'Valor Fixo'
            ),
            '2' => array(
                'value' => '2',
                'label' => 'Percentual'
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