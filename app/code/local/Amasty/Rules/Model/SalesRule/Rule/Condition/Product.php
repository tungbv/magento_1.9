<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Model_SalesRule_Rule_Condition_Product extends Mage_SalesRule_Model_Rule_Condition_Product
{

    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_sku'] = Mage::helper('amrules')->__('Custom Options SKU');

        if (Mage::getStoreConfig('amrules/general/options_values'))
            $attributes['quote_item_value'] = Mage::helper('amrules')->__('Custom Options Values');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $product = false;
        if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
            $product = $object->getProduct();
        } 
        else {
            $product = Mage::getModel('catalog/product')
                ->load($object->getProductId());
        }

        if (Mage::getStoreConfig('amrules/general/options_values')) {
            $options = $product->getTypeInstance(true)->getOrderOptions($product);
            $values = '';
            if (isset($options['options']))
                foreach ($options['options'] as $option)
                    $values .= '|'.$option['value'];

            $product->setQuoteItemValue($values);
        }

        $product->setQuoteItemSku($object->getSku());

        $object->setProduct($product);
        
        return parent::validate($object);
    }
}
