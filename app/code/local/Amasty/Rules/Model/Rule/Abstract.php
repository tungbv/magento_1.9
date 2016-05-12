<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Abstract
{
    protected $_priceSelector = 0;
    /**
     * Creates an array of the all prices in the cart
     *
     * @return array
     */

    public function setPriceSelector($value)
    {
        $this->_priceSelector = $value;
    }

    protected function disableDiscounts($item)
    {
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
    }

    protected function _getSortedCartPices($rule, $address)
    {
        $prices = array();
        $allitems = $this->getAllItems($address);
        $passItems = array();
        foreach ($allitems as $item) {

            if (!$item->getId()) {
                continue;
            }

            $passItems[$item->getId()] = $item;

            if (!in_array($item->getId(), Mage::helper('amrules')->getPassedItems())) {
                $this->disableDiscounts($item);
            }

            // we always skip child items and calculate discounts inside parents
            if (!Mage::getStoreConfig('amrules/general/bundle_separate')) {
                if ($item->getParentItemId() && $passItems[$item->getParentItemId()]->getProductType() == 'bundle') {
                    continue;
                }
            } else {
                if ($item->getProductType() == 'bundle') {
                    continue;
                }
            }

            if ($item->getParentItemId() && $passItems[$item->getParentItemId()]->getProductType() != 'bundle') {
                continue;
            }

            if (!$rule->getActions()->validate($item, true)) {
                continue;
            }

            if (Mage::getSingleton('amrules/promotions')->skip($rule,$item,$address )) continue;

            $price = $this->_getItemPrice($item, $rule->getPriceSelector());
            $basePrice = $this->_getItemBasePrice($item, $rule->getPriceSelector());

            // CE 1.3 version
            $qty = $this->_getItemQty($item);

            // we need to add discount from child item to parent
            // for bundles if we treat them as set of separate products,
            // not as one big product.

            $itemId = $item->getId();
            if (!Mage::getStoreConfig('amrules/general/bundle_separate')) {
                if ($item->getProductType() == 'bundle') {
                    $itemId = $item->getId();
                }
            }
            if ($price > 0) {
                for ($i = 0; $i < $qty; ++$i) {
                    $prices[] = array(
                        'price' => $price,
                        // don't call the function in a long cycle
                        'base_price' => $basePrice,
                        'id' => $itemId,
                    );
                }
            }
        } // foreach

        usort($prices, array(Mage::helper('amrules'), 'comparePrices'));

        return $prices;
    }

    /**
     * Determines qty of the discounted items
     *
     * @param Mage_Sales_Model_Rule $rule
     *
     * @return int qty
     */
    protected function _getQty($rule, $cartQty)
    {
        $discountQty = 1;
        $discountStep = (int)$rule->getDiscountStep();
        if ($cartQty == 0) {
            return $cartQty;
        }
        if ($rule->getSimpleAction() == Amasty_Rules_Helper_Data::TYPE_AMOUNT) {
            return $cartQty; // apply for all
        }

        if ($discountStep) {
            $discountQty = floor($cartQty / $discountStep);

            $maxDiscountQty = (int)$rule->getDiscountQty();
            if (!$maxDiscountQty) {
                $maxDiscountQty = $cartQty;
            }

            $discountQty = min($discountQty, $maxDiscountQty);

        }
        return $discountQty;
    }

    /**
     * Return item price in the store base currency
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     *
     * @return float
     */
    protected function _getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        ($price !== null) ? $price = $item->getBaseDiscountCalculationPrice() : $price = $item->getBaseCalculationPrice();
        switch ($this->_priceSelector) {
            case 1:
                $price -= $item->getBaseDiscountAmount()/$item->getQty();
                break;
            case 2:
                $price = $item->getProduct()->getPrice();
            break;
        }
        return $price;
    }

    /**
     * Return item price in currently active for quote currency
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     *
     * @return float
     */
    protected function _getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        ($price !== null) ? $price : $price = $item->getCalculationPrice();
        switch ($this->_priceSelector) {
            case 1:
                $price -= $item->getDiscountAmount()/$item->getQty();
                break;
            case 2:
                $price = $item->getQuote()->getStore()->convertPrice($item->getProduct()->getPrice());
                break;
        }
        return $price;
    }

    protected function _getItemQty($item)
    {
        if (!$item) return 1;
        //comatibility with CE 1.3 version
        return $item->getTotalQty() ? $item->getTotalQty() : $item->getQty();
    }

    protected function _getItemQtyBundle($item)
    {
        if (!$item) return 1;
        //comatibility with CE 1.3 version
        return $item->getQty() ? $item->getQty() : $item->getTotalQty();
    }

    protected function _skipBySteps($rule, $step, $i, $currQty, $qty)
    {
        $types = array(Amasty_Rules_Helper_Data::TYPE_EACH_N,
                       Amasty_Rules_Helper_Data::TYPE_FIXED,
                       Amasty_Rules_Helper_Data::TYPE_EACH_N_FIXDISC,
                       Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_PERC,
                       Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_DISC,
                       Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_FIX);
        $simpleAction = $rule->getSimpleAction();
        if (in_array($simpleAction, $types) && ($step > 1) && (($i + 1) % $step)) {
            return true;
        }

        $typeGroupN = Amasty_Rules_Helper_Data::TYPE_GROUP_N;
        $typeGroupNDisc = Amasty_Rules_Helper_Data::TYPE_GROUP_N_DISC;

        // introduce limit for each N with discount or each N with fixed.
        if ( (($currQty >= $qty) && ($simpleAction != $typeGroupN) && ($simpleAction != $typeGroupNDisc))
            || (($rule->getDiscountQty() <= $currQty) && ($rule->getDiscountQty()) && (($simpleAction == $typeGroupN)
                    || ($simpleAction == $typeGroupNDisc))) ) {
            return true;
        }
    }

    /**
     * @param $address
     *
     * @return mixed
     */
    public function getAllItems($address)
    {
        //we can take items from quote
        /*
         $items = $address->getQuote()->getAllItems();
	        if (!$items)
         */

        $items = $address->getAllNonNominalItems();
        if (!$items) { // CE 1.3 version
            $items = $address->getAllVisibleItems();
        }
        if (!$items) { // cart has virtual products
            $cart = Mage::getSingleton('checkout/cart');
            $items = $cart->getItems();
        }
        return $items;
    }

    public function hasDiscountItems($prices,$qty)
    {
        if (!$prices || $qty < 1) {
            return false;
        }
        return true;
    }

    public function prepareDiscount($discount, $address)
    {
        $items = $this->getAllItems($address);
        foreach ($items as $item) {
            if (   array_key_exists($item->getId(), $discount ) && $item->getProductType() == 'bundle' ) {
                $arr = $this->discountBundleChild( $address, $discount[$item->getId()],
                    $this->_getItemPrice($item), $this->_getItemBasePrice($item),$item->getId() );
                $discount = $discount + $arr;
            }
        }
        return $discount;
    }

    protected function discountBundleChild($address, $discount ,$bundlePrice,$bundleBasePrice ,$bundleId )
    {
        $r = array();
        if (!Mage::getStoreConfig('amrules/general/bundle_separate')) {
            $discountPerChild = $discount['discount'] / $bundlePrice;
            $baseDiscountPerChild = $discount['base_discount'] / $bundleBasePrice;
            foreach ($this->getAllItems($address) as $item) {
                if ($item->getParentItemId() && $item->getParentItemId()==$bundleId ) {
                    //$item->getProductType() == 'bundle'
                    // we always skip child items and calculate discounts inside parents
                    $price = $this->_getItemPrice($item);
                    $itemQty = $this->_getItemQtyBundle($item);
                    $basePrice = $this->_getItemBasePrice($item);
                    $r[$item->getId()]['discount'] = $price * $discountPerChild * $itemQty;
                    $r[$item->getId()]['base_discount'] = $basePrice * $baseDiscountPerChild * $itemQty;
                    $r[$item->getId()]['percent'] = $discountPerChild;
                }
            }
        }
        return $r;
    }

    protected function getItemById($id,$address)
    {
        $allItems = $this->getAllItems($address);
        foreach ($allItems as $item){
            if ($item->getItemId()==$id) return $item;
        }
    }

    protected function _getSumOfItems($prices)
    {
        $sum = 0;
        foreach($prices as $item){
            $sum += $item['price'];
        }
        return $sum;

    }
}