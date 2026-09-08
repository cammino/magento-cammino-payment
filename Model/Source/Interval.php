<?php

/**
 *
 * @category   Cammino
 * @package    Cammino_Payment
 * @author     Cammino Digital
 * @copyright 2026 Cammino Digital
 */

class Cammino_Payment_Model_Source_Interval
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'day', 'label' => 'Diário'),
            array('value' => 'week', 'label' => 'Semanal'),
            array('value' => 'month', 'label' => 'Mensal'),
        );
    }
}
