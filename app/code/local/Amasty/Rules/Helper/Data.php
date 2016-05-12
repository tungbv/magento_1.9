<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Helper_Data extends Mage_Core_Helper_Abstract 
{
    const TYPE_CHEAPEST             = 'the_cheapest';
    const TYPE_EXPENCIVE            = 'the_most_expencive';
  //  const WHOLE_CART                = 'whole_cart';
    const TYPE_FIXED                = 'fixed';
    const TYPE_EACH_N               = 'each_n';
    const TYPE_EACH_N_FIXDISC       = 'each_n_fixdisc';
    const TYPE_EACH_M_AFT_N_PERC    = 'each_m_aft_n_perc';
    const TYPE_EACH_M_AFT_N_DISC    = 'each_m_aft_n_disc';
    const TYPE_EACH_M_AFT_N_FIX     = 'each_m_aft_n_fix';
    const TYPE_GROUP_N              = 'group_n';
    const TYPE_GROUP_N_DISC         = 'group_n_disc';
    const TYPE_XY_PERCENT           = 'buy_x_get_y_percent';
    const TYPE_XY_FIXED             = 'buy_x_get_y_fixed';
    const TYPE_XY_FIXDISC           = 'buy_x_get_y_fixdisc';
    const TYPE_XN_PERCENT           = 'buy_x_get_n_percent';
    const TYPE_XN_FIXED             = 'buy_x_get_n_fixed';
    const TYPE_XN_FIXDISC           = 'buy_x_get_n_fixdisc';
    const TYPE_AFTER_N_FIXED        = 'after_n_fixed';
    const TYPE_AFTER_N_DISC         = 'after_n_disc';
    const TYPE_AFTER_N_FIXDISC      = 'after_n_fixdisc';
    const TYPE_AMOUNT               = 'money_amount';
    const TYPE_SETOF_PERCENT        = 'setof_percent';
    const TYPE_SETOF_FIXED          = 'setof_fixed';

    public $passedItems = array();

    public function addPassedItem($itemId)
    {
        $this->passedItems[] = $itemId;
    }

    public function getPassedItems()
    {
        return $this->passedItems;
    }

    public function getDiscountTypes($asOptions=false)
    {
        $types = array(
            self::TYPE_CHEAPEST             => $this->__('The Cheapest, also for Buy 1 get 1 free'),
            self::TYPE_EXPENCIVE            => $this->__('The Most Expensive'),
            self::TYPE_AMOUNT               => $this->__('Get $Y for each $X spent'),
         //   self::WHOLE_CART                => $this->__('Fixed amount discount to whole cart (beta)'),
			
            self::TYPE_EACH_N               => $this->__('Percent Discount: each 2-d, 4-th, 6-th with 15% 0ff'),
            self::TYPE_EACH_N_FIXDISC       => $this->__('Fixed Discount: each 3-d, 6-th, 9-th with $15 0ff'),
            self::TYPE_FIXED                => $this->__('Fixed Price: each 5th, 10th, 15th for $49'),


            self::TYPE_EACH_M_AFT_N_PERC    => $this->__('Percent Discount: each 1st, 3rd, 5th with 15% 0ff after 5 items added to the cart'),
            self::TYPE_EACH_M_AFT_N_DISC    => $this->__('Fixed Discount: each 3d, 7th, 11th with $15 0ff after 5 items added to the cart'),
            self::TYPE_EACH_M_AFT_N_FIX     => $this->__('Fixed Price: each 5th, 7th, 9th for $89.99 after 5 items added to the cart'),

            self::TYPE_GROUP_N              => $this->__('Fixed Price: Each 5 items for $50'),
            self::TYPE_GROUP_N_DISC         => $this->__('Percent Discount: Each 5 items with 10% off'),
			
            self::TYPE_XY_PERCENT           => $this->__('Percent Discount: Buy X get Y Free'),
            self::TYPE_XY_FIXDISC           => $this->__('Fixed Discount:  Buy X get Y with $10 Off .'),
            self::TYPE_XY_FIXED             => $this->__('Fixed Price: Buy X get Y for $9.99'),

            self::TYPE_XN_PERCENT           => $this->__('Percent Discount: Buy X get N of Y Free'),
            self::TYPE_XN_FIXDISC           => $this->__('Fixed Discount:  Buy X get N of Y with $10 Off .'),
            self::TYPE_XN_FIXED             => $this->__('Fixed Price: Buy X get N of Y for $9.99'),
			
            self::TYPE_AFTER_N_DISC         => $this->__('Percent Discount'),
            self::TYPE_AFTER_N_FIXDISC      => $this->__('Fixed Discount'),
            self::TYPE_AFTER_N_FIXED        => $this->__('Fixed Price'),
			
            self::TYPE_SETOF_PERCENT        => $this->__('Percent discount for product set'),
            self::TYPE_SETOF_FIXED          => $this->__('Fixed price for product set'),

        );
        
        if (!$asOptions){
		
			$groups = array(
				'Popular'         => array(self::TYPE_CHEAPEST, self::TYPE_EXPENCIVE, self::TYPE_AMOUNT/*, self::WHOLE_CART*/),
				'Buy X Get Y (X and Y are different products)' => array(self::TYPE_XY_PERCENT, self::TYPE_XY_FIXDISC, self::TYPE_XY_FIXED),
                'Buy X Get N of Y (X and Y are different products)' => array(self::TYPE_XN_PERCENT, self::TYPE_XN_FIXDISC, self::TYPE_XN_FIXED),
				'Each N-th'       => array(self::TYPE_EACH_N, self::TYPE_EACH_N_FIXDISC, self::TYPE_FIXED),
                'Each M-th after X-th' => array(self::TYPE_EACH_M_AFT_N_PERC, self::TYPE_EACH_M_AFT_N_DISC, self::TYPE_EACH_M_AFT_N_FIX),
				'Each Group of N' => array(self::TYPE_GROUP_N, self::TYPE_GROUP_N_DISC),
				'All products after N' => array(self::TYPE_AFTER_N_DISC, self::TYPE_AFTER_N_FIXDISC, self::TYPE_AFTER_N_FIXED),
				'Product Set (beta)'   => array(self::TYPE_SETOF_PERCENT, self::TYPE_SETOF_FIXED),
			);
			
			$result = array();
			
			foreach ($groups as $groupName => $groupActions){
				$values = array();
				foreach ($groupActions as $k){
					$values[] = array(
						'value' => $k, 
						'label' => $types[$k],                
					);
				}
				$result[] = array(
                    'label' => $this->__($groupName),
                    'value' => $values,				
				);
			}
            $types = $result;
        }

        return $types;
    }
    
    public function getMembership($created)
    {
        return round((time() - strtotime($created))  /60 / 60 /24);
    }

    public static function comparePrices($a, $b)
    {
        $res = ($a['price'] < $b['price']) ? -1 : 1;
        if ($a['price'] == $b['price']) {
            $res = ($a['id'] < $b['id']) ? -1 : 1;
            if ($a['id'] == $b['id']) {
                $res = 0;
            }
        }
        return $res;
    }

    public function getRuleCats($rule)
    {
        $promoCats = explode(",",$rule->getPromoCats());
        $promoCats = array_diff($promoCats, array(''));
        $promoCats = array_map('trim', $promoCats);
        return $promoCats;
    }

    public function getRuleSkus($rule)
    {
        $promoSku = explode(',', $rule->getPromoSku());
        $promoSku = array_diff($promoSku, array(''));
        $promoSku = array_map('trim', $promoSku);
        return $promoSku;
    }

    public function isConfigurablePromoItem($object ,$promoSku ){
        $productType  = $object->getProductType();

        if ($productType==Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ){
            foreach( $promoSku as $sku ){
                $clearSku = str_replace('-amconfigurable','',$sku);
                if ( strpos($sku,'-amconfigurable')!==false && strpos( $object->getProduct()->getSku() , $clearSku )!==false ) return true;
            }
        }
        return false;
    }
}