<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Model_CatalogRule_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    /**
     * Validate product attrbute value for condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $attrCode = $this->getAttribute();

        $objectCats = $object->getCategoryIds();

        if ( ('category_ids' == $attrCode) && isset( $objectCats ) ) {
	        return $this->validateAttribute($object->getCategoryIds());
        }
        return parent::validate($object);
    }
  
}
