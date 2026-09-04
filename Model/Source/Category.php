<?php

/**
 *
 * @category   Cammino
 * @package    Cammino_Payment
 * @author     Cammino Digital
 * @copyright 2026 Cammino Digital
 */

class Cammino_Payment_Model_Source_Category
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('level', array('gt' => 1))
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSort('name', 'ASC');

        foreach ($categories as $category) {
            $options[] = array(
                'value' => $category->getId(),
                'label' => $category->getName()
            );
        }

        return $options;
    }
}
