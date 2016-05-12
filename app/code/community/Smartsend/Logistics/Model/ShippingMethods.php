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
 * @folder		/app/code/community/Smartsend/Logistics/Model/ShippingMethods.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Model_ShippingMethods extends Varien_Object {

	/**
     * Function to return an array of shipping methods for a given carrier
     *
     * @param string $carrier is the carrier id formatted as 'groups[$carrier]'
     *
     * @return array of shipping methods with key as id and value as translated name
     */
    static public function getOptionArray($carrier) {

        $carrier_name = explode('groups[', $carrier);          //getting carrier name
        $carrier_name = explode(']', $carrier_name[1]);
        $carrier_name = $carrier_name[0];

		return self::getShippingMethodByCarrier($carrier_name);
		
    }
    
    /**
     * Function to return an array of shipping methods for a given carrier
     *
     * @param string $carrier_name is the id of the carrier
     *
     * @return array $shipping_methods of shipping methods with key as id and value as translated name
     */
    static public function getShippingMethodByCarrier($carrier_name) {
    
		switch ($carrier_name) {
    		case 'smartsendbring':
    			//All Bring shipping methods
            	$shipping_methods = array(
            		'private'				=> Mage::helper('logistics')->__('Private'),
            		'privatehome'			=> Mage::helper('logistics')->__('Private to home'),
            		'commercial'			=> Mage::helper('logistics')->__('Commercial'),
            		'commercial_bulksplit'	=> Mage::helper('logistics')->__('Commercial Bulksplit'),
            		'private_bulksplit'		=> Mage::helper('logistics')->__('Private Bulksplit'),
            		'privatehome_bulksplit'	=> Mage::helper('logistics')->__('Private to home Bulksplit'),
            		'express'				=> Mage::helper('logistics')->__('Express'),
            		'miniparcel'			=> Mage::helper('logistics')->__('Mini parcel'),
            		);
    			break;
    		case 'smartsendpostdanmark':
    			//All Post Danmark shipping methods
            	$shipping_methods = array(
            		'private'				=> Mage::helper('logistics')->__('Private'),
            		'privatehome'			=> Mage::helper('logistics')->__('Private to home'),
            		'commercial'			=> Mage::helper('logistics')->__('Commercial'),
            		'dpdclassic'			=> Mage::helper('logistics')->__('DPD Classic'),
           			'dpdguarantee'			=> Mage::helper('logistics')->__('DPD Guarantee'),
            		'valuemail'				=> Mage::helper('logistics')->__('Value mail'),
            		'privatesamsending'		=> Mage::helper('logistics')->__('Private collective'),
           		 	'privatepriority'		=> Mage::helper('logistics')->__('Private priority'),
            		'privateeconomy'		=> Mage::helper('logistics')->__('Private economy'),
            		'lastmile'				=> Mage::helper('logistics')->__('Service Logistics'),
           			'businesspriority'		=> Mage::helper('logistics')->__('Commercial priority'),
					);
    			break;
    		case 'smartsendposten':
    			//All Posten shipping methods
            	$shipping_methods = array(
            		'private'				=> Mage::helper('logistics')->__('Private'),
            		'privatehome'			=> Mage::helper('logistics')->__('Private to home'),
            		'commercial'			=> Mage::helper('logistics')->__('Commercial'),
            		'valuemail'				=> Mage::helper('logistics')->__('Value mail'),
            		'valuemailfirstclass'	=> Mage::helper('logistics')->__('Value mail first class'),
            		'valuemaileconomy'		=> Mage::helper('logistics')->__('Value mail economy'),
            		'maximail'				=> Mage::helper('logistics')->__('Maxi mail'),
            		);
    			break;
    		case 'smartsendgls':
    			//All GLS shipping methods
            	$shipping_methods = array(
            		'private'				=> Mage::helper('logistics')->__('Private'),
            		'privatehome'			=> Mage::helper('logistics')->__('Private to home'),
            		'commercial'			=> Mage::helper('logistics')->__('Commercial'),
            		);
    			break;
    		default:
    			$shipping_methods = array();
    			break;
    	}
        
        // If vConnect is installed remove pickup as shipping method
        if(Mage::helper('logistics/data')->isVconnetEnabled() == false) {
        	$shipping_methods = array('pickup' => Mage::helper('logistics')->__('Pickuppoint')) + $shipping_methods;
        }

        return $shipping_methods;
        
    }

    public function checkShippingFee($carrier, $shipping_country, $orderPrice, $orderWeight) {    //cheking shipping fees for the shipping method

        if (Mage::getStoreConfig('carriers/' . $carrier . '/price') != "") {
            $pickupShippingRates = unserialize(Mage::getStoreConfig('carriers/' . $carrier . '/price', Mage::app()->getStore()));                   //unserializing the shipping rates from the shipping rate table
        }
        
        $cheapestexpensive = Mage::getStoreConfig('carriers/' . $carrier . '/cheapestexpensive', Mage::app()->getStore());                  // get the Cheapest or most expensive for the admin system config
		if (!$cheapestexpensive) {
    		$cheapestexpensive = 0;
        }
        
        //This array will contain the valid shipping methods                
		$shippingmethods = array();  
        
        if (is_array($pickupShippingRates)) {

            foreach ($pickupShippingRates as $pickupShippingRate) {
            	$countries = strtoupper(str_replace(" ", "",$pickupShippingRate['countries']));
                $countries = explode(',', $countries);

				if( (in_array(strtoupper($shipping_country), $countries) || in_array('*', $countries))
					&& (float)$pickupShippingRate['orderminprice'] <= (float)$orderPrice
					&& ( (float)$pickupShippingRate['ordermaxprice'] >= (float)$orderPrice || (float)$pickupShippingRate['ordermaxprice'] == 0)
					&& (float)$pickupShippingRate['orderminweight'] <= (float)$orderWeight
					&& ( (float)$pickupShippingRate['ordermaxweight'] >= (float)$orderWeight || (float)$pickupShippingRate['ordermaxweight'] == 0)
					) {
					
					// The shipping rate is valid.
						
					if(isset($shippingmethods[$pickupShippingRate['methods']]) && $shippingmethods[$pickupShippingRate['methods']] != '') {
						//There is already a shipping method with the name in the array of valid shipping methods.
						if ( (int)$cheapestexpensive == 0 && ( (float) $shippingmethods[$pickupShippingRate['methods']] > (float) $pickupShippingRate['pickupshippingfee'] )) {
							//This method is cheaper and will override existing shipping method
							$shippingmethods[$pickupShippingRate['methods']] = array(
								'shippingfee'		=> $pickupShippingRate['pickupshippingfee'],
								'frontend_title'	=> $pickupShippingRate['method_name']
								);
						} elseif ( (int)$cheapestexpensive == 1 && ( (float) $shippingmethods[$rates['methods']] < (float) $pickupShippingRate['pickupshippingfee'] )) {
							//This method is more expensive and will override existing shipping method
							$shippingmethods[$pickupShippingRate['methods']] = array(
								'shippingfee'		=> $pickupShippingRate['pickupshippingfee'],
								'frontend_title'	=> $pickupShippingRate['method_name']
								);

						}
					} else {
						//Add the shipping method to the array of valid methods.
						$shippingmethods[$pickupShippingRate['methods']] = array(
								'shippingfee'		=> $pickupShippingRate['pickupshippingfee'],
								'frontend_title'	=> $pickupShippingRate['method_name']
								);

					}
			
                }
            }
        }

        return $shippingmethods;
    }

    
    public function excludedTax($shipping_method){
        $shippingmethods=array();
        
        $shippingmethods[]='smartsendpostdanmark_pickup';
        $shippingmethods[]='smartsendpostdanmark_private';
        $shippingmethods[]='smartsendpostdanmark_privatehome';
        $shippingmethods[]='smartsendpostdanmark_privatepriority';
        $shippingmethods[]='smartsendpostdanmark_valuemail';

        
        if(in_array($shipping_method,$shippingmethods)){
            return true;
        }else{
            return false;
        }
        
    }
}
