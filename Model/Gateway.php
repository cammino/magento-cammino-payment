<?php

/**
 * Option.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class Cammino_Googlemerchant_Model_Gateway
{
    /**
     * Function responsible for set array options
     *
     * @return object
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'pagarme', 'Pagar.me'],
            ['value' => 'pagseguro', 'Pagseguro'],
            ['value' => 'cielo', 'Cielo'],
            ['value' => 'tuna', 'Tuna'],
            ['value' => 'sicoob', 'Sicoob'],
            ['value' => 'zaaz', 'ZaaZ']
        ];
    }
}
