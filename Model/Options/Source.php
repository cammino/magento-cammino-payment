<?php
namespace Cammino\Payment\Model\Options;
 
class Source implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
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