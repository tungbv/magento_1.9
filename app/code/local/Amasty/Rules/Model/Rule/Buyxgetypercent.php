<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Buyxgetypercent
    extends Amasty_Rules_Model_Rule_Buyxgety
{
    function calculateDiscount($rule, $address, $quote)
    {
        // no conditions for Y elements
        if (!$rule->getPromoSku() && !$rule->getPromoCats()) {
            return array();
        }

        $arrX = $this->getTriggerElements($address, $rule);
        $realQty = $this->getTriggerElementQty($arrX);
        $maxQty = $this->_getQty($rule, $realQty);
        // find all allowed Y (discounted) elements and calculate total discount
        $currQty = 0; // there can be less elemnts to discont than $maxQty

        $passedItems = array();
        $r = array();
        $lastId = 0;
        foreach ($this->_getSortedCartPices($rule,$address) as $item) {
            $item = $this->getItemById($item['id'],$address);
            if ($currQty >= $maxQty) {
                break;
            }

            // what should we do with bundles when we treat them as
            // separate items
            $passedItems[$item->getId()] = $item;
            // we always skip child items and calculate discounts inside parents

            if (!$this->canProcessItem($item, $arrX, $passedItems)) {
                continue;
            }

            if (!$this->isDiscountedItem($rule, $item)) {
                continue;
            }

            $qty = $this->_getItemQty($item);

            if (($qty==$currQty) && ($lastId == $item->getId())) continue;

            $qty = min($maxQty - $currQty, $qty);
            $currQty += $qty;

            $percent = min(100, $rule->getDiscountAmount());
            $discount = (($qty * $this->_getItemPrice($item) ) * $percent) / 100;
            $baseDiscount = (($qty * $this->_getItemBasePrice($item) ) * $percent) / 100;

            $r[$item->getId()] = array();
            $r[$item->getId()]['discount'] = $discount;
            $r[$item->getId()]['base_discount'] = $baseDiscount;
            $r[$item->getId()]['percent'] = $percent;
            $lastId = $item->getId();
        }
        return $r;
    }
}