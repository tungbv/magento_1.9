<?php

/**
 * Smartsend_Logistics
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@smartsend.dk so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.smartsend.dk
 *
 * @folder		/app/code/community/Smartsend/Logistics/Model/Rewrite/Sales/Total/Quote/Tax.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */

class Smartsend_Logistics_Model_Rewrite_Sales_Total_Quote_Tax  extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    
    protected function _calculateShippingTax(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
       
        $taxRateRequest->setProductClassId($this->_config->getShippingTaxClass($this->_store));
        
        
        //Smart Send custom code - start
        $shipping_method= Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
        
        if(substr($shipping_method, 0, strlen('smartsend')) === 'smartsend') {
        
        	$carrier=explode('_',$shipping_method);
        	$smartsend_carrier=$carrier['0'];
        
        	$exclude_tax	= Mage::getStoreConfig("carriers/".$smartsend_carrier."/excludetax");
            $excludedMethod	= Mage::getModel('logistics/shippingMethods')->excludedTax($shipping_method);
        	if ($exclude_tax && $excludedMethod) {
                $taxRateRequest->setProductClassId(0);
        	}
        }
        //Smart Send custom code - end
        
      
        $rate           = $this->_calculator->getRate($taxRateRequest);
        $inclTax        = $address->getIsShippingInclTax();
        $shipping       = $address->getShippingTaxable();
        $baseShipping   = $address->getBaseShippingTaxable();
        $rateKey        = (string)$rate;

        $hiddenTax      = null;
        $baseHiddenTax  = null;
        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $tax        = $this->_calculator->calcTaxAmount($shipping, $rate, $inclTax, false);
                $baseTax    = $this->_calculator->calcTaxAmount($baseShipping, $rate, $inclTax, false);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $address->getShippingDiscountAmount();
                $baseDiscountAmount = $address->getBaseShippingDiscountAmount();
                $tax = $this->_calculator->calcTaxAmount(
                    $shipping - $discountAmount,
                    $rate,
                    $inclTax,
                    false
                );
                $baseTax = $this->_calculator->calcTaxAmount(
                    $baseShipping - $baseDiscountAmount,
                    $rate,
                    $inclTax,
                    false
                );
                break;
        }

        if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
            $this->_addAmount(max(0, $tax));
            $this->_addBaseAmount(max(0, $baseTax));
            $tax        = $this->_deltaRound($tax, $rate, $inclTax);
            $baseTax    = $this->_deltaRound($baseTax, $rate, $inclTax, 'base');
        } else {
            $tax        = $this->_calculator->round($tax);
            $baseTax    = $this->_calculator->round($baseTax);
            $this->_addAmount(max(0, $tax));
            $this->_addBaseAmount(max(0, $baseTax));
        }

        if ($inclTax && !empty($discountAmount)) {
            $hiddenTax      = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
            $baseHiddenTax  = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false);
            $this->_hiddenTaxes[] = array(
                'rate_key'   => $rateKey,
                'value'      => $hiddenTax,
                'base_value' => $baseHiddenTax,
                'incl_tax'   => $inclTax,
            );
        }

        $address->setShippingTaxAmount(max(0, $tax));
        $address->setBaseShippingTaxAmount(max(0, $baseTax));
        $applied = $this->_calculator->getAppliedRates($taxRateRequest);
        $this->_saveAppliedTaxes($address, $applied, $tax, $baseTax, $rate);

        return $this;
    }

}

