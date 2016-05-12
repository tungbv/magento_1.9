<?php

/**
 * Smartsend_Logistics Order class
 *
 * Create order objects that is included in the final Smart Send label API callout.
 *
 * LICENSE
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
 * @class		Smartsend_Logistics_Model_Order
 * @folder		/app/code/community/Smartsend/Logistics/Model/Order.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Smart Send
 * @url			www.smartsend.dk
 * @version		7.1.0rc1
 *
 *	// Overall functions
 *	public function _construct()
 *	public function setOrderObject($order_object)
 *	public function setReturn($return=false)
 *	public function getFinalOrder()
 *
 *	// Logical functions
 *	public function isSmartsendOrVConnect()
 *	public function isPickup()
 *	public function isReturn()
 *	public function isSmartsend()
 *	public function isVconnect()
 *	public function isPickupSmartsend()
 *	public function isPickupVconnect()
 *	public function isSmartDelivery()
 *
 *	// Shipping functions
 *	protected function getShippingCarrierAndMethod()
 *	protected function renameShippingCarrier($shipping_string)
 *	protected function renameShippingMethod($shipping_string)
 *	public function getShippingCarrier($format=0)
 *	public function getShippingMethod()
 *	
 *	// Order set functions
 *	public function setInfo()
 *	public function setReceiver()
 *	public function setSender()
 *	public function setAgent()
 *	public function setService()
 *	public function setParcels()
 *
 *	// Order get functions
 *	public function getPickupId()
 *	public function getPickupData()
 *	public function getPickupDataVconnect()
 *	public function getSettingsCarrier()
 *	public function getWaybill($string,$country)
 *	protected function getCustomerCommentStringPositions()
 *	protected function getCustomerCommentStringPositionsFlex()
 *	protected function getCustomerCommentStringPositionsSmartDelivery()
 *	public function getCustomerCommentTrimmed()
 *	public function getFlexDeliveryNote()
 *	protected function getFlexDeliveryNoteFromCustomerComment()
 *	protected function getSmartDeliveryTimeInterval($time)
 *	protected function getFreetext()
 *
 *
 *	// This class is called by using the code:
 *		$order = new Smartsend_Logistics_Order();
 *		$order->setOrderObject($order_object);
 *		$order->setReturn(true);
 *		try{
 *			$order->setInfo();
 *			$order->setReceiver();
 *			$order->setSender();
 *			$order->setAgent();
 *			$order->setService();
 *			$order->setParcels();
 *	
 *			//All done. Add to request.
 *			$request_array[] = $order->getFinalOrder();
 *		} catch (Exception $e) {
 *			echo 'Caught exception: ',  $e->getMessage(), "\n";
 *		}
 *
*/

class Smartsend_Logistics_Model_Order extends Mage_Core_Model_Abstract {

	protected $_order;
	protected $_info;
	protected $_receiver;
	protected $_sender;
	protected $_agent;
	protected $_service;
	protected $_parcels = array();
	protected $_return = false;
	
	protected $_test = false;

/*****************************************************************************************
 * Overall functions
 ****************************************************************************************/
	
	/**
	* 
	* Set the order object
	* @param object $order_object order object
	*/
	public function setOrderObject($order_object) {
		$this->_order = $order_object;
	}
	
	/**
	* 
	* Set wheter or not the label is a return label
	* @param boolean $return wheter or not the label is a return label
	*/
	public function setReturn($return=false) {
		$this->_return = $return;
	}
	
	/**
	* 
	* Construct the order array that is used to create the final JSON request.
	* @return array
	*/
	public function getFinalOrder() {
		return array_merge($this->_info,array(
			'receiver'	=> $this->_receiver,
			'sender'	=> $this->_sender,
			'agent'		=> $this->_agent,
			'service'	=> $this->_service,
			'parcels'	=> $this->_parcels
			));	
	}


/*****************************************************************************************
 * Logical functions: Functions to return true/false for different statements
 ****************************************************************************************/

	/**
	* 
	* Check if order is placed by a SmartSend or a vConnect shipping method
	* @return boolean
	*/
	public function isSmartsendOrVConnect() {

		if($this->isSmartsend() == true) {
			return true;
		} elseif($this->isVconnect() == true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* 
	* Check if order is a pickup shipping method from SmartSend or vConnect
	* @return boolean
	*/	
	public function isPickup() {

		if($this->isPickupSmartsend() == true) {
			return true;
		} elseif($this->isPickupVconnect() == true) {
			return true;
		} else {
			return false;
		}

	}

	/**
	* 
	* Check if the label is a return label for the order
	* @return boolean
	*/	
	public function isReturn()	{
		return $this->_return;
	}

	/**
	* 
	* Check if order is placed by a SmartSend shipping method
	* @return boolean
	*/
	public function isSmartsend() {
	
		$method = strtolower($this->getShippingId());
	
		//Check if shipping methode starts with 'smartsend'
		if(substr($method, 0, strlen('smartsend')) === 'smartsend') {
			return true;
		} else {
			return false;
		}
	
	}

	/**
	* 
	* Check if order is placed by a vConnect shipping method
	* @return boolean
	*/
	public function isVconnect() {
	
		$method = strtolower($this->getShippingId());
	
		//Check if shipping methode starts with 'vconnect' or 'vc'
		if(substr($method, 0, strlen('vconnect')) === 'vconnect') {
			return true;
		} elseif(substr($method, 0, strlen('vc')) === 'vc') {
			return true;
		} else {
			return false;
		}
	
	}
	
	/**
	* 
	* Check if order is a pickup shipping method from SmartSend
	* @return boolean
	*/	
	public function isPickupSmartsend() {
	
		if($this->isSmartsend() == true) {
			$method = $this->getShippingMethod();
	
			//Check if shipping methode ends with 'pickup'
			if(substr($method, -strlen('pickup')) === 'pickup') {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	
	}

	/**
	* 
	* Check if order is a pickup shipping method from vConnect
	* @return boolean
	*/	
	public function isPickupVconnect() {
	
		if($this->isVconnect() == true) {
			$method = $this->getShippingMethod();
	
			//Check if shipping methode ends with 'pickup'
			if(substr($method, -strlen('pickup')) === 'pickup') {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	* 
	* Check if Smart Delivery is active
	* @return boolean
	*/	
	public function isSmartDelivery() {
	
		if($this->getCustomerCommentStringPositionsSmartDelivery() !== false) {
			return true;
		} else {
			return false;
		}
		
	}
	
/*****************************************************************************************
 * Shipping functions
 ****************************************************************************************/
	
	/**
	* 
	* Get shipping carrier and method
	*	0: @string shipping carrier. Example: 'postdanmark'
	*	1: @string shipping method. Example: 'private'
	* @return array
	*/
	protected function getShippingCarrierAndMethod() {
		
		// Retreive unique shipping id. Example: 'postdanmark_private'
		$shipping_string = $this->getShippingId();
		
		$carrier = $this->renameShippingCarrier($shipping_string);
		$method = $this->renameShippingMethod($shipping_string);
		
		if($this->isReturn() == true) {
			switch ($carrier) {
				case 'postdanmark':
					$settings = $this->getSettingsPostdanmark();
					$shipping_string = $settings['return'];
					break;
				case 'posten':
					$settings = $this->getSettingsPosten();
					$return_shipping_method = $settings['return'];
					break;
				case 'gls':
					$settings = $this->getSettingsGls();
					$return_shipping_method = $settings['return'];
					break;
				case 'bring':
					$settings = $this->getSettingsBring();
					$return_shipping_method = $settings['return'];
					break;
				default:
					//Change this code for each CMS system
					throw new Exception($this->_errors[2301]);
			}
			
			// Check if the carrier must be changed
			try{
				$carrier = $this->renameShippingCarrier($shipping_string);
			} catch(Exception $e) {
				// Do nothing if no return shipping carrier is found
			}
			
			// Check if the method must be changed
			try{
				$new_method = $this->renameShippingMethod($shipping_string);
				if(isset($new_method) && $new_method != '' && $new_method != 'auto') {
					$method = $new_method;
				}
			} catch(Exception $e) {
				// Do nothing if no return shipping method is found
			}
		}
		
		if(!isset($carrier) || $carrier == '') {
			throw new Exception($this->_errors[2302]);
		} elseif(!isset($method) || $method == '') {
			throw new Exception($this->_errors[2303]);
		} else {
			return array($carrier,$method);
		}
		
	}
	
	/**
	* 
	* Find the shipping carrier from a string and return as lower case single word
	* @param string $carrier to be determined. Example: 'smartsendpostdanmark_private' would give 'postdanmark'
	* @return array
	*/
	protected function renameShippingCarrier($shipping_string) {
		
		$shipping_string = strtolower($shipping_string);
	
	// Smart Send shipping methods
		if(substr($shipping_string, 0, strlen('smartsendpickup')) === 'smartsendpickup' || substr($shipping_string, 0, strlen('smartsend_pickup')) === 'smartsend_pickup') {
			$carrier = $this->getPickupCarrier();
			if(!isset($carrier) || $carrier == '') {
				throw new Exception($this->_errors[2304]);
			}
		} elseif(substr($shipping_string, 0, strlen('smartsendbring')) === 'smartsendbring' || substr($shipping_string, 0, strlen('smartsend_bring')) === 'smartsend_bring') {
			$carrier = 'bring';
		} elseif(substr($shipping_string, 0, strlen('smartsendgls')) === 'smartsendgls' || substr($shipping_string, 0, strlen('smartsend_gls')) === 'smartsend_gls') {
			$carrier = 'gls';
		} elseif(substr($shipping_string, 0, strlen('smartsendpostdanmark')) === 'smartsendpostdanmark' || substr($shipping_string, 0, strlen('smartsend_postdanmark')) === 'smartsend_postdanmark') {
			$carrier = 'postdanmark';
		} elseif(substr($shipping_string, 0, strlen('smartsendposten')) === 'smartsendposten' || substr($shipping_string, 0, strlen('smartsend_posten')) === 'smartsend_posten') {
			$carrier = 'posten';
			
	// vConnect All-in-1 module shipping methods
		} elseif(substr($shipping_string, 0, strlen('vconnect_postnord_dk')) === 'vconnect_postnord_dk') {
			$carrier = 'postdanmark';
		} elseif(substr($shipping_string, 0, strlen('vconnect_postnord_se')) === 'vconnect_postnord_se') {
			$carrier = 'posten';
		} elseif(substr($shipping_string, 0, strlen('vconnect_postnord_no')) === 'vconnect_postnord_no') {
			$carrier = 'postnordnorway';
		} elseif(substr($shipping_string, 0, strlen('vconnect_postnord_fi')) === 'vconnect_postnord_fi') {
			$carrier = 'postnordfinland';
			
	// Old vConnect shipping methods
		} elseif(substr($shipping_string, 0, strlen('vconnect_postdanmark')) === 'vconnect_postdanmark' || substr($shipping_string, 0, strlen('vc_postdanmark')) === 'vc_postdanmark' || substr($shipping_string, 0, strlen('vc_allinone_vconnectpostdanmark')) === 'vc_allinone_vconnectpostdanmark') {
			$carrier = 'postdanmark';
		} elseif(substr($shipping_string, 0, strlen('vconnect_posten')) === 'vconnect_posten' || substr($shipping_string, 0, strlen('vc_posten')) === 'vc_posten' || substr($shipping_string, 0, strlen('vc_allinone_vconnectposten')) === 'vc_allinone_vconnectposten') {
			$carrier = 'posten';
		} elseif(substr($shipping_string, 0, strlen('vconnect_postnord')) === 'vconnect_postnord' || substr($shipping_string, 0, strlen('vc_postnord')) === 'vc_postnord' || substr($shipping_string, 0, strlen('vc_allinone_vconnectpostnord')) === 'vc_allinone_vconnectpostnord') {
			$carrier = 'postdanmark';
		} elseif(substr($shipping_string, 0, strlen('vconnect_gls')) === 'vconnect_gls' || substr($shipping_string, 0, strlen('vc_gls')) === 'vc_gls') {
			$carrier = 'gls';
		} elseif(substr($shipping_string, 0, strlen('vconnect_bring')) === 'vconnect_bring' || substr($shipping_string, 0, strlen('vc_bring')) === 'vc_bring') {
			$carrier = 'bring';
		} elseif(substr($shipping_string, 0, strlen('vconnect_pdkalpha')) === 'vconnect_pdkalpha') {
			$carrier = 'postdanmark';
			
	// If the shipping method is unknown throw an error
		} else {
			throw new Exception( $this->_errors[2305] .': '. $shipping_string );
		}
	
		return $carrier;
	}
	
	/**
	* 
	* Find the shipping method from a string and return as lower case single word
	* @param string $carrier to be determined. Example: 'smartsendpostdanmark_private' would give 'private'
	* @return array
	*/
	protected function renameShippingMethod($shipping_string) {
		
		$shipping_string = strtolower($shipping_string);

		if(substr($shipping_string, -strlen('pickup')) === 'pickup') {
			$method = 'pickup';
		} elseif(substr($shipping_string, -strlen('private')) === 'private') {
			$method = 'private';
		} elseif(substr($shipping_string, -strlen('privatehome')) === 'privatehome') {
			$method = 'privatehome';
		} elseif(substr($shipping_string, -strlen('commercial')) === 'commercial') {
			$method = 'commercial';
		} elseif(substr($shipping_string, -strlen('express')) === 'express') {
			$method = 'express';
		} elseif(substr($shipping_string, -strlen('privatesamsending')) === 'privatesamsending') {
			$method = 'privatesamsending';
		} elseif(substr($shipping_string, -strlen('privatepriority')) === 'privatepriority') {
			$method = 'privatepriority';
		} elseif(substr($shipping_string, -strlen('privateeconomy')) === 'privateeconomy') {
			$method = 'privateeconomy';
		} elseif(substr($shipping_string, -strlen('lastmile')) === 'lastmile') {
			$method = 'lastmile';
		} elseif(substr($shipping_string, -strlen('businesspriority')) === 'businesspriority') {
			$method = 'businesspriority';
		} elseif(substr($shipping_string, -strlen('dpdclassic')) === 'dpdclassic') {
			$method = 'dpdclassic';
		} elseif(substr($shipping_string, -strlen('dpdguarantee')) === 'dpdguarantee') {
			$method = 'dpdguarantee';
		} elseif(substr($shipping_string, -strlen('valuemail')) === 'valuemail') {
			$method = 'valuemail';
		} elseif(substr($shipping_string, -strlen('valuemailfirstclass')) === 'valuemailfirstclass') {
			$method = 'valuemailfirstclass';
		} elseif(substr($shipping_string, -strlen('valuemaileconomy')) === 'valuemaileconomy') {
			$method = 'valuemaileconomy';
		} elseif(substr($shipping_string, -strlen('maximail')) === 'maximail') {
			$method = 'maximail';
		} elseif(substr($shipping_string, -strlen('miniparcel')) === 'miniparcel') {
			$method = 'miniparcel';
		} elseif(substr($shipping_string, -strlen('private_bulksplit')) === 'private_bulksplit') {
			$method = 'private_bulksplit';
		} elseif(substr($shipping_string, -strlen('privatehome_bulksplit')) === 'privatehome_bulksplit') {
			$method = 'privatehome_bulksplit';
		} elseif(substr($shipping_string, -strlen('commercial_bulksplit')) === 'commercial_bulksplit') {
			$method = 'commercial_bulksplit';
		} elseif(substr($shipping_string, -strlen('bestway')) === 'bestway') {
			$method = 'pickup';
		} elseif(substr($shipping_string, -strlen('postdanmark')) === 'postdanmark') {
			$method = 'pickup';
		} elseif(substr($shipping_string, -strlen('posten')) === 'posten') {
			$method = 'pickup';
		} elseif(substr($shipping_string, -strlen('postnord')) === 'postnord') {
			$method = 'pickup';
		} elseif(substr($shipping_string, -strlen('bring')) === 'bring') {
			$method = 'pickup';
		} elseif(substr($shipping_string, -strlen('gls')) === 'gls') {
			$method = 'pickup';
	// Support for vConnect shipping method 'pdkalpha'
		} elseif(substr($shipping_string, 0, strlen('vconnect_pdkalpha')) === 'vconnect_pdkalpha') {
			$method = 'lastmile';
		} else {
	// If the shipping method is unknown throw an error
			throw new Exception( $this->_errors[2306] .': '. $shipping_string );
		}
	
		return $method;
	}
	
	/**
	* 
	* Get shipping carrier
	* Format 0: lowercase single word (default). Example: 'postdanmark'
	* Format 1: Capilized user friendly output. Example: 'Post Danmark'
	* @param int $format defines the format of the shipping carrier
	* @return string
	*/
	public function getShippingCarrier($format=0) {
		$shipping_info = $this->getShippingCarrierAndMethod();
		
		if(isset($shipping_info[0]) && $shipping_info[0] != '') {
			$carrier_lowcase = strtolower($shipping_info[0]);
		} else {
			throw new Exception( $this->_errors[2307] );
		}
		
		switch ($carrier_lowcase) {
			case 'postdanmark':
				if($format == 0) {
					return 'postdanmark';
				} else {
					return 'Post Danmark';
				}
				break;
			case 'posten':
				if($format == 0) {
					return 'posten';
				} else {
					return 'Posten';
				}
				break;
			case 'gls':
				if($format == 0) {
					return 'gls';
				} else {
					return 'GLS';
				}
				break;
			case 'bring':
				if($format == 0) {
					return 'bring';
				} else {
					return 'Bring';
				}
				break;
			default:
				throw new Exception( $this->_errors[2308] );
		}
	
	}
	
	/**
	* 
	* Get shipping method
	* Example: 'pickup'
	* @return string
	*/
	public function getShippingMethod() {
		$shipping_info = $this->getShippingCarrierAndMethod();
		
		if(isset($shipping_info[1]) && $shipping_info[1] != '') {
			return $shipping_info[1];
		} else {
			//Change this code for each CMS system
			throw new Exception( $this->_errors[2309] );
		}
	}
 	

/*****************************************************************************************
 * Order set functions: Functions to set order parameters
 ****************************************************************************************/

	/**
	* 
	* Set the meta data for the order
	*/
	public function setInfo() {
	
		$carrier 	= $this->getShippingCarrier();
		$method 	= $this->getShippingMethod();
		
		$settings 	= $this->getSettingsCarrier();
		$type 		= (isset($settings['format']) ? $settings['format'] : null);

 		$this->_info = array(
 			'orderno'		=> $this->getOrderId(),
 			'type'			=> $type,
   			'reference'		=> $this->getOrderReference(),
   			'carrier'		=> $carrier,
   			'method'		=> $method,
   			'return'		=> $this->isReturn(),
   			'totalprice'	=> $this->getOrderPriceTotal(),
   			'shipprice'		=> $this->getOrderPriceShipping(),
   			'currency'		=> $this->getOrderPriceCurrency(),
   			'test'			=> $this->_test,
 			);
	
	}
	
	/**
	* 
	* Set the receiver information
	*/
	public function setReceiver() {
	
		if($this->isPickupVconnect() == true) {
			$this->_receiver = $this->getBillingAddress();
		} else {
			$this->_receiver = $this->getShippingAddress();
		}
	
	}
	
	/**
	* 
	* Set the sender information
	*/
	public function setSender() {
	
		$carrier 	= $this->getShippingCarrier();
		
		switch ($carrier) {
			case 'postdanmark':
				$settings 	= $this->getSettingsPostdanmark();
				$sender 	= array(
					'senderid' 	=> (isset($settings['quickid']) ? $settings['quickid'] : null),
 					'company'	=> null,
					'name1'		=> null,
					'name2'		=> null,
					'address1'	=> null,
					'address2'	=> null,
					'zip'		=> null,
					'city'		=> null,
					'country'	=> null,
					'sms'		=> null,
					'mail'		=> null
 					);
				break;
			case 'posten':
				$settings 	= $this->getSettingsPosten();
				$sender 	= array(
					'senderid' 	=> (isset($settings['quickid']) ? $settings['quickid'] : null),
 					'company'	=> null,
					'name1'		=> null,
					'name2'		=> null,
					'address1'	=> null,
					'address2'	=> null,
					'zip'		=> null,
					'city'		=> null,
					'country'	=> null,
					'sms'		=> null,
					'mail'		=> null
 					);
				break;
			default:
				$sender 	= array(
					'senderid' 	=> null,
 					'company'	=> null,
					'name1'		=> null,
					'name2'		=> null,
					'address1'	=> null,
					'address2'	=> null,
					'zip'		=> null,
					'city'		=> null,
					'country'	=> null,
					'sms'		=> null,
					'mail'		=> null
 					);	
		}
		
		$this->_sender = $sender;
	
	}
	
	/**
	* 
	* Set the agen information
	*/
	public function setAgent() {
	
		if($this->isPickup() == true) {
			$this->_agent = $this->getPickupData();
		} else {
			$this->_agent = null;
		}
	
	}
	
	/**
	* 
	* Set the services that is used for the order
	*/
	public function setService() {
	
		$settings = $this->getSettingsCarrier();
		
		$this->_service = array(
			'notemail'				=> ($settings['notemail'] == 1 ? $this->_receiver['mail'] : null),
			'notesms'				=> ($settings['notesms'] == 1 ? $this->_receiver['sms'] : null),
			'prenote'				=> $settings['prenote'],
			'prenote_from'			=> $settings['prenote_from'],
			'prenote_receiver'		=> ($settings['prenote_receiver'] == '' ? $this->_receiver['mail'] : $settings['prenote_receiver']),
			'prenote_message'		=> ($settings['prenote_message'] != '' ? $settings['prenote_message'] : null),
			'flex'					=> ($this->getFlexDeliveryNote() ? true : null),
			'waybillid'				=> $this->getWaybill($settings['waybillid'],$this->_receiver['country']),
			'smartdelivery'			=> $this->isSmartDelivery(),
			'smartdelivery_start'	=> $this->getSmartDeliveryTimeInterval('start'),
			'smartdelivery_end'		=> $this->getSmartDeliveryTimeInterval('end'),
			);
	
	}
	
	/**
	* 
	* Set the parcels. Each parcel contains items.
	*/
	public function setParcels() {

		//Get all shipments for the order
		$shipments = $this->getShipments();
		
		if(!empty($shipments)) {
			//Go through shipments and check for Track & Trace
			foreach($shipments as $shipment) {
				if($this->isReturn() == true) {
					//Add shipment to order object as a parcel
					$this->addShipment($shipment);
				} else {
					if( !$this->getShipmentTrace($shipment) ) {
						//Add shipment to order object as a parcel
						$this->addShipment($shipment);
					}
				}
			}
			
			if(empty($this->_parcels)) {
				throw new Exception( $this->_errors[2401] );
			}
		} else {
			if($this->getUnshippedItems() != null) {
				$this->createShipment();
			} else {
				throw new Exception( $this->_errors[2402] );
			}
		}
	
		if(empty($this->_parcels)) {
			throw new Exception( $this->_errors[2401] );
		}

	}
	
/*****************************************************************************************
 * Order get functions: Functions to get order parameters
 ****************************************************************************************/
 	
 	/**
	* 
	* Get pickup id of delivery point
	* @return string
	*/	
	public function getPickupId() {
	
		$pickupdata = $this->getPickupData();
		return (isset($pickupdata['id']) ? $pickupdata['id'] : null);
	
	}

	/**
	* 
	* Get pickup data for delivery point
	* @return array
	*/	
	public function getPickupData() {

		if($this->isPickupSmartsend() == true) {
			return $this->getPickupDataSmartsend();
		} elseif($this->isPickupVconnect() == true) {
			return $this->getPickupDataVconnect();
		} else {
			throw new Exception( $this->_errors[2501] );
		}

	}
	
	/**
	* 
	* Get pickup data for a vConnect delivery point
	* @return array
	*/	
	public function getPickupDataVconnect() {
	
		$billing_address = $this->getShippingAddress();

		$pacsoftServicePoint 		= str_replace(' ', '', $billing_address['address2']); 	//remove spaces
		$pacsoftServicePointArray 	= explode(":",$pacsoftServicePoint); 			//devide into a array by :

		if ( isset($pacsoftServicePointArray) && ( strtolower($pacsoftServicePointArray[0]) == strtolower('ServicePointID') ) ||  strtolower($pacsoftServicePointArray[0]) == strtolower('Pakkeshop') ){
			$pickupData = array(
				'id' 		=> $pacsoftServicePointArray[1]."-".time()."-".rand(9999,10000),
				'agentno'	=> $pacsoftServicePointArray[1],
				'agenttype'	=> ($this->getShippingCarrier() == 'postdanmark' ? 'PDK' : null),
				'company' 	=> $billing_address['company'],
				'name1' 	=> $billing_address['name1'],
				'name2' 	=> $billing_address['name2'],
				'address1'	=> $billing_address['address1'],
				'address2' 	=> null,
				'city'		=> $billing_address['city'],
				'zip'		=> $billing_address['zip'],
				'country'	=> $billing_address['country'],
				'sms' 		=> null,
				'mail' 		=> null,
				);
		
			return $pickupData;
		
		} else {
			return null;
		}
	
	}
 	
 	/**
	* 
	* Get the settings from the carrier that would be used if this is a normal label.
	* This is not nessesary the same as the actual carrier if one uses a different carrier
	* for return labels.
	* @return array
	*/
 	public function getSettingsCarrier() {
 	
 		$carrier = $this->getShippingCarrier();
		switch ($carrier) {
			case 'postdanmark':
				$settings = $this->getSettingsPostdanmark();
				break;
			case 'posten':
				$settings = $this->getSettingsPosten();
				break;
			case 'gls':
				$settings = $this->getSettingsGls();
				break;
			case 'bring':
				$settings = $this->getSettingsBring();
				break;
			default:
				$settings = null;
		}
		
		return $settings;
		
	}
	
	/**
	 *
	 * Function to return if waybill id if any
	 * @return string
	 */
	public function getWaybill($string,$country) {

		//Devide string into array
		$array = explode(";", $string);
	
		//Remove empty fields
		$array = array_filter($array);
	
		//Check if there is entries
		if(!empty($array) || !is_array($array)) {
			if(strpos($array[0], ',') !== FALSE) {
		
				$new_array = array();
				foreach($array as $element) {
					//Devide string into array
					$line = explode(",", $element);
					if(isset($line[0])) {
						$new_array[$line[0]] = $line[1];
					}
				}
			
				if(isset($new_array[$country])) {
					return $new_array[$country];
				} elseif(isset($new_array["*"])) {
					return $new_array["*"];
				}
			} else {
				//Only one id is entered
				return $array[0];
			}
		} else {
			return null;
		}

	}
	
	/**
	* 
	* Get array with string posistion of custom information
	* @return array / false
	*/
	protected function getCustomerCommentStringPositions() {
		//Search for 'Flex:' and 'SmartDeliver' (case-insensitive). Sorter acending
		$strpos_array = array();
		
		//Search for 'Flex:' in order customer comment
		if($this->getCustomerCommentStringPositionsFlex() !== false) {
			$strpos_array[] = $this->getCustomerCommentStringPositionsFlex();
		}
		
		//Search for 'SmartDeliver:' in order customer comment
		if($this->getCustomerCommentStringPositionsSmartDelivery() !== false) {
			$strpos_array[] = $this->getCustomerCommentStringPositionsSmartDelivery();
		}

		if(!empty($strpos_array)) {
			sort($strpos_array);
			return $strpos_array;
		} else {
			return false;
		}

	}
	
	/**
	* 
	* Get array with string posistion of custom information
	* @return int / false
	*/
	protected function getCustomerCommentStringPositionsFlex() {
		return stripos($this->getCustomerComment(),"Flex:");
	}
	
	/**
	* 
	* Get array with string posistion of custom information
	* @return int / false
	*/
	protected function getCustomerCommentStringPositionsSmartDelivery() {
		return stripos($this->getCustomerComment(),"SmartDelivery:");
	}

	/**
	* 
	* Get trimmed order comment only with the text entered by the customer
	* @return string / null
	*/
	public function getCustomerCommentTrimmed() {
		if( $this->getCustomerCommentStringPositions()) {
			if(min( $this->getCustomerCommentStringPositions() ) > 0) {
				return substr($this->getCustomerComment(),0,min( $this->getCustomerCommentStringPositions() ));
			} else {
				return null;
			}
		} else {
			return $this->getCustomerComment();
		}
	}
	
	/**
	* 
	* Get the Flex delivery note (where to place the parcel)
	* @return string / null
	*/
	public function getFlexDeliveryNote() {
		if( $this->getFlexDeliveryNoteFromMysql() ) {
			return $this->getFlexDeliveryNoteFromMysql();
		} elseif( $this->getFlexDeliveryNoteFromCustomerComment() ) {
			return $this->getFlexDeliveryNoteFromCustomerComment();
		} else {
			return null;
		}
		
	}

	/**
	* 
	* Get the Flex delivery note (where to place the parcel) from the order comment
	* @return string / null
	*/
	protected function getFlexDeliveryNoteFromCustomerComment() {
		$strpost_flexdeliver = false;
		
		//Search for 'Flex:' in order customer comment
		if(stripos($this->getCustomerComment(),"Flex:") !== false) {
			$strpost_flexdeliver = stripos($this->getCustomerComment(),"Flex:");
		}
		
		if($strpost_flexdeliver !== false) {
			// Check if there is any more information in the OrderComment and cut from there
			$strpost_flexdeliver_end = 0;
			foreach ($this->getCustomerCommentStringPositions() as $position) {
				if ($position > $strpost_flexdeliver) {
					$strpost_flexdeliver_end = $position;
					break;
				}
			}
			$strpost_flexdeliver = $strpost_flexdeliver + strlen("Flex:");
			if($strpost_flexdeliver_end) {
				return substr($this->getCustomerComment(),$strpost_flexdeliver,$strpost_flexdeliver_end-$strpost_flexdeliver);
			} else {
				return substr($this->getCustomerComment(),$strpost_flexdeliver);
			}
		} else {
			return null;
		}
	
	}
	
	/**
	 *
	 * Function to return Smart Deliver time interval
	 * @return string
	 */
	protected function getSmartDeliveryTimeInterval($time) {
		
		if($this->getCustomerCommentStringPositionsSmartDelivery() !== false) {
			$comment = $this->getCustomerComment();
			$position = $this->getCustomerCommentStringPositionsSmartDelivery();
		
			if($position !== false) {
				$string_array = explode(":", substr($comment,$position));
				if(is_array($string_array) && isset($string_array[0]) && $string_array[1] && $string_array[2]) {
					if($time == 'start') {
						return str_replace(".", ":", trim($string_array[1]));
					} elseif($time == 'end') {
						return str_replace(".", ":", trim($string_array[2]));
					} else {
						return null;
					}
				} else {
					return null;
				}
			} else {
				return null;
			}
		} else {
			return null;
		}
		
	}
	
	/**
	 *
	 * Function to return freetext that is displayed on the label
	 * @return string
	 */
	protected function getFreetext() {
		// If there is a flexdelivery note, return this
		if( $this->getFlexDeliveryNote() ) {
			return $this->getFlexDeliveryNote();
		}
		// If the setting is to include the order comment, include a trimmed comment
		elseif( $this->getSettingIncludeOrderComment() ) {
			return $this->getCustomerCommentTrimmed();
		}
		// Otherwise return an empty string
		else {
			return null;
		}
	}

}