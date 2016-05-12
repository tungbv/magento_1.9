<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Setoffixed extends Amasty_Rules_Model_Rule_Setof
{
    function calculateDiscount($rule, $address, $quote)
    {
        $r = array();
        list ( $qtySkus,$qtyCats,$sortedProducts ) = $this->prepareSetRule($rule,$address,$quote);
        if (!$qtyCats && !$qtySkus) {
            return $r;
        }
        $minQty = $this->getMinQty($rule,$qtySkus,$qtyCats);

        $discountedQtyByItem = array();
        $summaryPrice = 0;
        $cats = $this->getCats($rule->getPromoCats());
        $skus = $this->getSkus($rule->getPromoSku());
        foreach ($sortedProducts as $itemId => $price) {
            $item = $quote->getItemById($itemId);
            if (!$item) return false;

            $itemQty = $this->_getItemQty($item);
            if ($rule->getPromoSku() && in_array($item->getSku(), $skus)) {
                $discountedQty = min($itemQty, $qtySkus[$item->getSku()]);
                $discountedQtyByItem[$itemId] = $discountedQty;
                $qtySkus[$item->getSku()] -= $discountedQty;
                $summaryPrice += $discountedQty * ($this->_getItemPrice($item));
            }

            if ($rule->getPromoCats() && array_intersect($item->getProduct()->getCategoryIds(), $cats)) {
                foreach (array_intersect($item->getProduct()->getCategoryIds(), $cats) as $category) {
                    $discountedQty = min($itemQty, $qtyCats[$category]);
                    $discountedQtyByItem[$itemId] = $discountedQty;
                    $qtyCats[$category] -= $discountedQty;
                    $summaryPrice += $discountedQty * ($this->_getItemPrice($item));
                }
            }
        }

        foreach ($sortedProducts as $itemId => $price) {
            $item = $quote->getItemById($itemId);
            if (!array_key_exists( $itemId,$discountedQtyByItem )) continue;
            if (!$item) {
                continue;
            }

            $percentDiscount = 1 - $rule->getDiscountAmount() * $minQty / $summaryPrice;
            $r[$item->getId()]['discount'] = ($this->_getItemPrice($item)) * ($percentDiscount)
                * $discountedQtyByItem[$itemId];
            $r[$item->getId()]['base_discount'] = ($this->_getItemBasePrice($item)) * ($percentDiscount)
                * $discountedQtyByItem[$itemId];
            $r[$item->getId()]['percent'] = $percentDiscount;
            $lastId = $item->getId();

        }

        return $r;
    }

    private function getMinQty($rule,$qtySkus,$qtyCats)
    {
        $minQty = 0;
        if ($qtySkus) {
            if ($rule->getPromoSku()) {
                $minQty = min($qtySkus);
                foreach ($qtySkus as $key => $qtySku) {
                    $qtySkus[$key] = $minQty;
                }
            }
        }

        if ($qtyCats) {
            if ($rule->getPromoCats()) {
                $minQty = min($qtyCats);
                foreach ($qtyCats as $key => $qtyCat) {
                    $qtyCats[$key] = $minQty;
                }
            }
        }
        return $minQty;
    }
}