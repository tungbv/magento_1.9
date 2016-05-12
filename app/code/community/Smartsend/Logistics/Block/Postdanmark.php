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
 * @folder		/app/code/community/Smartsend/Logistics/Block/Postdanmark.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Block_Postdanmark extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {

    public function __construct() {
        $this->setTemplate('logistics/pickup.phtml');
    }

    public function getPickupData() {
        $checkOut 	= Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();         //getting shipping address from checkout quote
        
        $street 	= $checkOut->getStreet();
        $street 	= implode(' ', $street);             // splitting the street by " "(space)
        $city 		= $checkOut->getCity();
        $postcode 	= $checkOut->getPostcode();
        $country 	= $checkOut->getCountry();
        $carriers 	= 'postdanmark';

        $pickup = Mage::getModel('logistics/api_pickups');

        $pickup->_post($street, $city, $postcode, $country, $carriers);        // get the pickup points for the postdanmark carrier

        if ($pickup->getPickupPoints() != false) {
            return $pickup->getPickupPoints();
        } else {
            return false;
        }
    }

    public function getFlexData($carrier) {

        $array = array(
        	$this->__('By the frontdoor')		=> $this->__('By the frontdoor'),
        	$this->__('By the carport')			=> $this->__('By the carport'),
        	$this->__('By the back dor')		=> $this->__('By the back dor'),
        	$this->__('I have Modtagerflex')	=> $this->__('I have Modtagerflex'),
        	$this->__('Can be left unattended')	=> $this->__('Can be left unattended')
        	);
        $array2 = array();
        $flexShippingMethods = Mage::getStoreConfig('carriers/smartsendpostdanmark/flexdelivery');
        $flexShippingMethods = explode(',', $flexShippingMethods);

        if (in_array($carrier, $flexShippingMethods)) {
            return $array;
        } else {
            return $array2;
        }
    }

}
