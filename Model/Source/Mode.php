<?php

/**
 *
 * @category   Cammino
 * @package    Cammino_Payment
 * @author     Cammino Digital
 * @copyright 2023 Cammino Digital
 */

class Cammino_Payment_Model_Source_Mode
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array(
            'production' => array(
                'value' => 'production',
                'label' => 'Produção'
            ),
            'homolog' => array(
                'value' => 'homolog',
                'label' => 'Testes'
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