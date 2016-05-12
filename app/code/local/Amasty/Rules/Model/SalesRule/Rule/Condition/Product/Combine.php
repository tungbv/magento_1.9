<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Model_SalesRule_Rule_Condition_Product_Combine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine
{
    public function validate(Varien_Object $object, $checkX = false)
    {
        // for optimization if we no conditions
        /*
        if (!$this->getConditions()) {
            return true;
        }
*/
        //remember original product
        $origProduct = $object->getProduct();
        $origSku     = $object->getSku();

        $action = $this->getRule()->getSimpleAction();

        if ( strpos( $action , "buy_x_get_" ) !== false && $action!==Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION) {

            $promoCats = Mage::helper('amrules')->getRuleCats($this->getRule());
            $promoSku = Mage::helper('amrules')->getRuleSkus($this->getRule());
            $itemSku = $object->getSku();
            $itemCats = $object->getCategoryIds();

            if (!$itemCats) $itemCats = $object->getProduct()->getCategoryIds();

            $parent = $object->getParentItem();

            if (Mage::helper('amrules')->isConfigurablePromoItem($object,$promoSku)) return true;

            if ($parent) {
                $parentType = $parent->getProductType();
                if ($parentType == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $itemSku = $object->getParentItem()->getProduct()->getSku();
                    $itemCats = $object->getParentItem()->getProduct()->getCategoryIds();
                }
            }

            if ( in_array( $itemSku,$promoSku )  ){
                return true;
            }

            if (  !is_null($itemCats)  && array_intersect( $promoCats, $itemCats ) ){
                return true;
            }
            if (!$checkX) {
                return false;
            }
        }

        if ($object->getHasChildren() && $object->getProductType() == 'configurable'){
            foreach ($object->getChildren() as $child) {
                // only one itereation.
                $categoryIds = array_merge($child->getProduct()->getCategoryIds(),$origProduct->getCategoryIds());
                $categoryIds = array_unique($categoryIds);
                $object->setProduct($child->getProduct());
                $object->setSku($child->getSku());
                $object->getProduct()->setCategoryIds($categoryIds);
            }
        }


        //$result = @Mage_Rule_Model_Condition_Combine::validate($object);

        $validator = new Mage_Rule_Model_Condition_Combine();
        $validator->setData($this->getData());
        $result = $validator->validate($object);
        $this->setData($validator->getData());

        if ($origProduct){
            // restore original product
            $object->setProduct($origProduct);    
            $object->setSku($origSku);    
        }        

        return $result;       
    }

    public function getGrandparentClass($thing) {
        if (is_object($thing)) {
            $thing = get_class($thing);
        }
        $class = new ReflectionClass($thing);
        return $class->getParentClass()->getParentClass()->getName();
    }
}
