<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Setofpercent extends Amasty_Rules_Model_Rule_Setof
{
    function calculateDiscount($rule, $address, $quote)
    {
        list ( $qtySkus,$qtyCats,$sortedProducts ) = $this->prepareSetRule($rule,$address,$quote);

        $r = array();
        $discountedQtyByItem = array();
        $cats = $this->getCats($rule->getPromoCats());
        $skus = $this->getSkus($rule->getPromoSku());
        foreach ($sortedProducts as $itemId => $price) {
            $item = $quote->getItemById($itemId);
            $itemQty = $this->_getItemQty($item);
            if ($rule->getPromoSku() && in_array($item->getSku(), $skus)) {
                $discountedQty = min($itemQty, $qtySkus[$item->getSku()]);
                $discountedQtyByItem[$itemId] = $discountedQty;
                $qtySkus[$item->getSku()] -= $discountedQty;
            }

            if ( $rule->getPromoCats() && array_intersect($item->getProduct()->getCategoryIds(), $cats) ) {
                foreach (array_intersect($item->getProduct()->getCategoryIds(), $cats) as $category) {
                    $discountedQty = min($itemQty, $qtyCats[$category]);
                    $discountedQtyByItem[$itemId] = $discountedQty;
                    $qtyCats[$category] -= $discountedQty;
                }
            }

            $percent = min(100, $rule->getDiscountAmount());
            $r[$item->getId()]['discount'] = ($this->_getItemPrice($item)) * ($percent / 100) * $discountedQty;
            $r[$item->getId()]['base_discount'] = ($this->_getItemBasePrice($item)) * ($percent / 100) * $discountedQty;
            $r[$item->getId()]['percent'] = $percent;

            $lastId = $item->getId();
        }

        return $r;
    }
}