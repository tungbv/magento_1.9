<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Groupn extends Amasty_Rules_Model_Rule_Abstract
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

        $countPrices = count($prices);

        //we must check all items price and compare with group price
        $totalPrice = 0;
        foreach ($prices as $price){
            $totalPrice +=  $price['base_price'];
            
        }

        if ( $totalPrice < $rule->getDiscountAmount() ){
            return $r;
        }

        foreach ($prices as $i => $price) {
            if ( $this->_skipBySteps($rule,$step,$i,$currQty,$qty) ) continue;

            ++$currQty;

            if ($i < $countPrices - ($countPrices % $step)) {
                $discount = $price['price'] - $quote->getStore()->convertPrice($rule->getDiscountAmount()) / $step;
                //if ($discount<0) $r[$lastId]['discount'] += $discount;
                $baseDiscount = $price['base_price'] - $rule->getDiscountAmount() / $step;
                //if ($baseDiscount<0) $r[$lastId]['base_discount'] += $baseDiscount;
                $percentage = $discount * 100 / $price['price'];
                /*if ($percentage<0) {
                    $percentage = $r[$lastId]['discount'] * 100 / $price['price'];
                }*/
            } else {
                $discount = 0;
                $baseDiscount = 0;
            }

            if ($price['id'] != $lastId) {
                $lastId = intVal($price['id']);
                $r[$lastId] = array();
                $r[$lastId]['discount'] = $discount;
                $r[$lastId]['base_discount'] = $baseDiscount;
                $r[$lastId]['percent'] = $percentage;
                $r[$lastId]['price'] = $price['price'];
                $r[$lastId]['base_price'] = $price['base_price'];
            } else {
                $r[$lastId]['discount'] += $discount;
                $r[$lastId]['base_discount'] += $baseDiscount;
                $r[$lastId]['percent'] = $percentage;
                $r[$lastId]['price'] = $price['price'];
                $r[$lastId]['base_price'] = $price['base_price'];
            }
        }

        $r = $this->spreadDiscount($r);

        return $r;
    }

    protected function spreadDiscount($r)
    {
        $negativeDiscount = 0;
        $negativeBaseDiscount = 0;
        $discountSum = 0;
        $result = array();
        foreach ($r as $key=>$rule) {
            $discountSum += $rule['discount'];
            if ($rule['discount']<0) {
                $negativeDiscount += abs($rule['discount']);
                $negativeBaseDiscount +=abs($rule['base_discount']);
            } else {
                $result[$key] = $rule;
            }
        }
        if ($discountSum<0) return array();
        if ($negativeDiscount>0) {
            foreach ($result as $key=>$res) {
                if (($res['discount']-$negativeDiscount)<0) {
                    $result[$key]['discount'] = 0;
                    $result[$key]['percent'] = 0;
                    $negativeDiscount -= $res['discount'];
                } else {
                    $result[$key]['discount'] -=$negativeDiscount;
                    $result[$key]['percent'] = $result[$key]['discount'] * 100 / $res['price'];
                    $negativeDiscount = 0;
                }
                if (($res['base_discount']-$negativeBaseDiscount)<0) {
                    $result[$key]['base_discount'] = 0;
                    $negativeDiscount -= $res['base_discount'];
                } else {
                    $result[$key]['base_discount'] -=$negativeBaseDiscount;
                    $negativeBaseDiscount = 0;
                }
            }
        }
        return $result;
    }
}