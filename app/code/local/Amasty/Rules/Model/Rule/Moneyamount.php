<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Moneyamount extends Amasty_Rules_Model_Rule_Abstract
{
    function calculateDiscount($rule, $address, $quote)
    {
        $r = array();
        $prices = $this->_getSortedCartPices($rule, $address);
        $qty = $this->_getQty($rule, count($prices));
        if (!$this->hasDiscountItems($prices,$qty)) {
            return $r;
        }
        $prices = array_slice($prices, -$qty, $qty);
        $step = (int)$rule->getDiscountStep();

        $baseSum = 0;
        foreach ($prices as $price) {
            $baseSum += $price['base_price'];
        }

        $timesToApply = floor($baseSum / max(1, $step));

        $maxTimesToApply = max(0, (int)$rule->getDiscountQty()); // remove negative values if any
        if ($maxTimesToApply) {
            $timesToApply = min($timesToApply, $maxTimesToApply);
        }

        $baseAmount = $timesToApply * $rule->getDiscountAmount();

        if ($rule->getSimpleAction() == Amasty_Rules_Helper_Data::TYPE_AMOUNT) {
            if ($baseAmount <= 0.001) {
                return $r;
            }
            $percentage = $baseAmount / $baseSum;
        }

        $currQty = 0;
        $lastId = -1;
        foreach ($prices as $i => $price) {
            if ( $this->_skipBySteps($rule,$step,$i,$currQty,$qty) ) continue;
            $currQty++;

            $discount = $price['price'] * $percentage;
            $baseDiscount = $price['base_price'] * $percentage;
            if ($price['id'] != $lastId) {
                $lastId = intVal($price['id']);
                $r[$lastId] = array();
                $r[$lastId]['discount'] = $discount;
                $r[$lastId]['base_discount'] = $baseDiscount;
                $r[$lastId]['percent'] = $percentage;
            } else {
                $r[$lastId]['discount'] += $discount;
                $r[$lastId]['base_discount'] += $baseDiscount;
                $r[$lastId]['percent'] = $percentage;
            }
        }
        return $r;
    }
}