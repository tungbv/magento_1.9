<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Fixed extends Amasty_Rules_Model_Rule_Abstract
{
    function calculateDiscount($rule, $address, $quote)
    {
        $r = array();
        $prices = $this->_getSortedCartPices($rule, $address);
        $qty = $this->_getQty($rule, count($prices));
        if (!$this->hasDiscountItems($prices,$qty)) {
            return $r;
        }

        $prices = array_reverse($prices);

        $currQty = 0;
        $lastId = -1;
        $step = (int)$rule->getDiscountStep();
        foreach ($prices as $i => $price) {
            if ( $this->_skipBySteps($rule,$step,$i,$currQty,$qty) ) continue;

            $currQty++;

            $discount = $price['price'] - $quote->getStore()->convertPrice($rule->getDiscountAmount());
            $baseDiscount = $price['base_price'] - $rule->getDiscountAmount();

            if ($price['id'] != $lastId) {
                $lastId = intVal($price['id']);
                $r[$lastId] = array();
                $r[$lastId]['discount'] = $discount;
                $r[$lastId]['base_discount'] = $baseDiscount;
            } else {
                $r[$lastId]['discount'] += $discount;
                $r[$lastId]['base_discount'] += $baseDiscount;
            }
        }

        return $r;

    }
}




