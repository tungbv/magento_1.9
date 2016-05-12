<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Wholecart extends Amasty_Rules_Model_Rule_Abstract
{
    function calculateDiscount($rule, $address, $quote)
    {
        $r = array();

        $prices = $this->_getSortedCartPices($rule, $address);

        $sumOfDiscount = 0;
        $sumOfBaseDiscount = 0;
        foreach ($prices as $priceItem){
            $item = $this->getItemById($priceItem['id'],$address);
            $sumOfDiscount += $item->getDiscountAmount();
            $sumOfBaseDiscount += $item->getBaseDiscountAmount();
        }

        $sum = $this->_getSumOfItems($prices) - $sumOfDiscount;
        $percentage = 0;
        if ($sum > 0)
            $percentage = floatVal($rule->getDiscountAmount()) / $sum;
        $qty = $this->_getQty($rule, count($prices));

        if (!$this->hasDiscountItems($prices,$qty)) {
            return $r;
        }

        $lastId = -1;

        foreach ($prices as $i => $price) {
            $item = $this->getItemById($price['id'],$address);
            $discount = ($price['price'] - $item->getDiscountAmount())  * $percentage;
            $baseDiscount = ($price['base_price'] - $item->getBaseDiscountAmount()) * $percentage;
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