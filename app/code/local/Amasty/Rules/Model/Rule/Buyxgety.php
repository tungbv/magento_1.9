<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Buyxgety extends Amasty_Rules_Model_Rule_Abstract
{
    protected $_passedItems = array();

    public function getTriggerElements($address,$rule)
    {
        // find all X (trigger) elements
        $arrX = array();
        $allItems = $this->getAllItems($address);

        foreach ($this->_getSortedCartPices($rule,$address) as $item) {
            $item = $this->getItemById($item['id'],$address);
            if ($item->getParentItemId()) {
                continue;
            }

            if (!$item->getId()) {
                continue;
            }

            $promoCats = Mage::helper('amrules')->getRuleCats($rule);
            $promoSku = Mage::helper('amrules')->getRuleSkus($rule);

            if ( Mage::helper('amrules')->isConfigurablePromoItem($item,$promoSku)  ) continue;

            if (!$rule->getActions()->validate($item, true)) {
                continue;
            }

            if ( in_array( $item->getSku(),$promoSku )  ) {
                continue;
            }
            if (!$promoSku) {
                $itemCats = $item->getCategoryIds();
                if (!$itemCats) $itemCats = $item->getProduct()->getCategoryIds();
                if ( !is_null( $itemCats ) && array_intersect( $promoCats, $itemCats ) ) {
                    continue;
                }
            }
            $arrX[$item->getId()] = $item;
        }
        return $arrX;
    }

    public function getTriggerElementQty($arrX)
    {
        $realQty = 0;
        foreach ($arrX as $el) {
            $realQty += $this->_getItemQty($el);
        }
        return $realQty;
    }

    public function isDiscountedItem($rule,$item)
    {
        $product = $item->getProduct();
        // for configurable product we need to use the child
        if ($item->getHasChildren() && $item->getProductType() == 'configurable') {
            foreach ($item->getChildren() as $child) {
                // one iteration only
                $product = $child->getProduct();
                // can work for credit cards, but does not work with PayPal, so it is commented out
                //$categoryIds = array_merge($product->getCategoryIds(), $item->getProduct()->getCategoryIds());
                //$product->setCategoryIds($categoryIds);
            }
        }
        $cats = Mage::helper('amrules')->getRuleCats($rule);
        $sku = Mage::helper('amrules')->getRuleSkus($rule);

        $currentSku = $product->getSku();
        $currentCats = $product->getCategoryIds();

        $parent = $item->getParentItem();

        if ( Mage::helper('amrules')->isConfigurablePromoItem($item,$sku)  ) return true;

        if (isset($parent)) {
            $parentType = $parent->getProductType();
            if ($parentType == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $currentSku = $item->getParentItem()->getProduct()->getSku();
                $currentCats = $item->getParentItem()->getProduct()->getCategoryIds();
            }
        }

        if (!in_array($currentSku, $sku) && !array_intersect($cats, $currentCats)) {
            return false;
        }
        return true;
    }

    public function canProcessItem($item,$arrX,$passedItems)
    {
        if (!$item->getId()) {
            return false;
        }
        /*
        if (!Mage::getStoreConfig('amrules/general/bundle_separate')) {
            if ($item->getParentItemId() && $passedItems[$item->getParentItemId()]->getProductType() == 'bundle') {
                return false;
            }
        } else {
            if ($item->getProductType() == 'bundle') {
                return false;
            }
        }

        if ($item->getParentItemId() && $passedItems[$item->getParentItemId()]->getProductType() != 'bundle') {
            return false;
        }*/

        //do not apply discont on triggers
        if (isset($arrX[$item->getId()])) {
            return false;
        }

        return true;
    }

    protected function _getNQty($rule, $realQty)
    {
        if ($rule->getDiscountStep() > $realQty) {
            return 0;
        } else {
            $rule->getDiscountStep() == 0 ? $step=1:$step=$rule->getDiscountStep();
            $count =  floor( $realQty / $step ) * $rule->getData('buy_x_get_n');
            if ($rule->getDiscountQty()) {
                $nqty  = min($count , $rule->getDiscountQty() );
            } else {
                $nqty = $count;
            }
            return $nqty;//min($count , $rule->getDiscountQty() );
        }
    }
}