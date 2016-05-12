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
 * @folder		/app/code/community/Smartsend/Logistics/Model/Carrier/Postdanmark.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Model_Carrier_Postdanmark extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'smartsendpostdanmark';

    public function isTrackingAvailable() {
    	return true;
	}

    public function getFormBlock() {
        return 'logistics/postdanmark';
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
    	// Check that the carrier is not deactive
        if (!Mage::getStoreConfig('carriers/' . $this->_code . '/active')) {
            return false;
        }

		// Carrier information
        $handling = Mage::getStoreConfig('carriers/' . $this->_code . '/handling');
        $result = Mage::getModel('shipping/rate_result'); // Table rate for shipping methods
        $show = true; // Always true
        
        // Shipping country
        $shipping_country = $request->getDestCountryId();
                
        // Order subtotal price (price without shipping) WITHOUT tax
        // $order_subtotal = $request->getOrderSubtotal(); //NOT WORKING
        $order_subtotal = $request->getPackageValueWithDiscount();
        /*
        // iterate over all item and add item price inc tax
        $items = $request->getAllItems(); // Can include bundled items AND the subitems
		$order_subtotal = 0;
		foreach ($items as $item){
			$order_subtotal += $item->getRowTotalInclTax();
		}
		*/
        
        // Order total weight
        $order_weight = $request->getPackageWeight();
                
        if ($show) { // This if condition is just to demonstrate how to return success and error in shipping methods$method = Mage::getModel('shipping/rate_result_method');
            $carrier = $this->_code;
            
            // Chek the table rates for valid shipping method
            $shipping_methods_array = Mage::getModel('logistics/shippingMethods')->checkShippingFee($carrier, $shipping_country, $order_subtotal, $order_weight);

			if(is_array($shipping_methods_array)) {
				foreach ($shipping_methods_array as $shipping_method_code => $shipping_method) {

					// Create a shipping method
					$method = Mage::getModel('shipping/rate_result_method');
					$method->setCarrier($this->_code);
					$method->setMethod($shipping_method_code);
					$method->setCarrierTitle( $this->getConfigData('title') );
					$method->setMethodTitle( $shipping_method['frontend_title'] );
					$method->setPrice( $shipping_method['shippingfee'] );
					$method->setCost( $shipping_method['shippingfee'] );
					
					// Add shipping method to collection of valid methods
					$result->append($method);
				}
			} else {
				$error = Mage::getModel('shipping/rate_result_error');
            	$error->setCarrier($this->_code);
            	$error->setCarrierTitle($this->getConfigData('name'));
            	$error->setErrorMessage($this->getConfigData('specificerrmsg'));
            	$result->append($error);
			}
        } else {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('name'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }
        return $result;
    }

	/**
	 * Method is required by the interface
	 *
	 * @return array of key-value pairs of all available methods
	 */
    public function getAllowedMethods() {
        return Mage::getModel('logistics/shippingMethods')->getShippingMethodByCarrier( $this->_code );
    }

}
