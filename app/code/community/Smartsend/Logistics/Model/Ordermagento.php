<?php

/**
 * Smartsend_Logistics Order class
 *
 * Create order objects that is included in the final Smart Send label API callout.
 * These are the CMS dependent functions that is used by the order class.
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
 *
 * @class		Smartsend_Logistics_Model_Ordermagento
 * @folder		/app/code/community/Smartsend/Logistics/Model/Ordermagento.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Smart Send
 * @url			www.smartsend.dk
 * @version		7.1.0rc1
 *
 *	// Order
 *	public function getShippingId()
 *	public function getPickupCarrier()
 *	public function getOrderId()
 *	public function getOrderReference()
 *	public function getOrderPriceTotal()
 *	public function getOrderPriceShipping()
 *	public function getOrderPriceCurrency()
 *	public function getCustomerComment()
 *	public function getShippingAddress()
 *	public function getBillingAddress()
 *	public function getPickupDataSmartsend()
 *	protected function getFlexDeliveryNoteFromMysql()
 *	
 *	// Settings
 *	public function getSettingsPostdanmark()
 *	public function getSettingsPosten()
 *	public function getSettingsGls()
 *	public function getSettingsBring()
 *	protected function getSettingIncludeOrderComment
 *
 *	// Shipments
 *	public function getShipments()
 *	public function getShipmentTrace($shipment)
 *	public function getShipmentWeight($shipment)
 *	protected function getUnshippedItems()
 *	protected function createShipment()
 *	protected function addShipment($shipment)
 *	protected function addItem($item)
 *	
 */
 
class Smartsend_Logistics_Model_Ordermagento extends Smartsend_Logistics_Model_Order {

	public $_errors = array(
		// Shipping
		2301	=>	'Unable to determine the shipping method used for return parcels',
		2302	=>	'Unable to determine the shipping carrier',
		2303	=>	'Unable to determine the shipping method',
		2304	=>	'Unable to determine carrier for pickup shipping method',
		2305	=>	'Unsupported carrier',
		2306	=>	'Unable to determine shipping method for carrier',
		2307	=>	'Unable to determine shipping carrier',
		2308	=>	'Unknown shipping carrier',
		2309	=>	'Unable to determine shipping method',
		// Order set
		2401	=>	'All packages has been shipped. No parcels without trace code exists. Remove existing tracecodes to re-generate labels.',
		2402	=>	'No order items to ship',
		2403	=>	'No parcels to ship',
		// Order get
		2501	=>	'Trying to access pickup data for an order that is not a pickup point order',
		);
		
	public function __construct() {
	
		// Translate the error string of the $_error array
		foreach($this->_errors as $key=>$value) {
			$this->_errors[$key] = Mage::helper('logistics')->__($value);
		}
	}

/*****************************************************************************************
 * Order
 ****************************************************************************************/

	/**
	* 
	* Get shipping name/id
	* @return string
	*/
	public function getShippingId() {
	
		$shipMethod_id = $this->_order->getShippingMethod();
		
		return $shipMethod_id; //return unique id of shipping method
	
	}

	/**
	* 
	* Get carrier name based on the pickup information.
	* Used if the shipping method is 'closest pickup point'
	* @return string
	* ## Depricted function ##
	*/
	public function getPickupCarrier() {
	/*
		$pickupModel = Mage::getModel('logistics/pickup');
		$pickupData = $pickupModel->getCollection()->addFieldToFilter('order_id', $this->_order->getOrderId() )->getFirstItem();        //pickup data 
		if ($pickupData->getData()) {
			$carrier = $pickupData->getCarrier();
		} else {
			$carrier = null;
		}
	*/
	}
 
 	/**
	* 
	* Get the order id (SQL id)
	* @return string
	*/
 	public function getOrderId() {
 	
		return $this->_order->getId();
		
 	}
 	
 	/**
	* 
	* Get the order refernce (the one the customer sees)
	* @return string
	*/
 	public function getOrderReference() {
 	
 		return $this->_order->getIncrementId();
 	
 	}
 	
 	/**
	* 
	* Get total price of order including tax
	* @return float
	*/
 	public function getOrderPriceTotal() {
 	
		return $this->_order->getGrandTotal();
		
 	}
 	
 	/**
	* 
	* Get shipping costs including tax
	* @return float
	*/
 	public function getOrderPriceShipping() {
 	
		return $this->_order->getShippingAmount();
		
	}
 	
 	/**
	* 
	* Get the currency used for the order
	* @return string
	*/
 	public function getOrderPriceCurrency() {
 	
		return $this->_order->getOrderCurrencyCode();
		
 	}
 	
 	/**
	* 
	* Get the comment that the user provided during checkout
	* @return string
	*/
 	public function getCustomerComment() {
 		$comments_collection_object = $this->_order->getStatusHistoryCollection(true);
		return $comments_collection_object->getLastItem()->getComment();
 	}
 	
 	/**
	* 
	* Get the shipping address information
	* @return array
	*/
 	public function getShippingAddress() {
 	
		return array(
			'receiverid'=> ($this->_order->getShippingAddress()->getId() != '' ? $this->_order->getShippingAddress()->getId() : 'guest-'.rand(100000,999999)),
			'company'	=> $this->_order->getShippingAddress()->getCompany(),
			'name1' 	=> $this->_order->getShippingAddress()->getFirstname() .' '. $this->_order->getShippingAddress()->getLastname(),
			'name2'		=> null,
			'address1'	=> $this->_order->getShippingAddress()->getStreet(1),
			'address2'	=> $this->_order->getShippingAddress()->getStreet(2),
			'city'		=> $this->_order->getShippingAddress()->getCity(),
			'zip'		=> $this->_order->getShippingAddress()->getPostcode(),
			'country'	=> $this->_order->getShippingAddress()->getCountry_id(),
			'sms'		=> $this->_order->getShippingAddress()->getTelephone(),
			'mail'		=> $this->_order->getShippingAddress()->getEmail()
			);
			
 	}
 	
 	/**
	* 
	* Get the shipping address information
	* @return array
	*/
 	public function getBillingAddress() {
 	
		return array(
			'receiverid'=> ($this->_order->getBillingAddress()->getId() != '' ? $this->_order->getBillingAddress()->getId() : 'guest-'.rand(100000,999999)),
			'company'	=> $this->_order->getBillingAddress()->getCompany(),
			'name1' 	=> $this->_order->getBillingAddress()->getFirstname() .' '. $this->_order->getBillingAddress()->getLastname(),
			'name2'		=> null,
			'address1'	=> $this->_order->getBillingAddress()->getStreet(1),
			'address2'	=> $this->_order->getBillingAddress()->getStreet(2),
			'city'		=> $this->_order->getBillingAddress()->getCity(),
			'zip'		=> $this->_order->getBillingAddress()->getPostcode(),
			'country'	=> $this->_order->getBillingAddress()->getCountry_id(),
			'sms'		=> $this->_order->getBillingAddress()->getTelephone(),
			'mail'		=> $this->_order->getBillingAddress()->getEmail()
			);
				
 	}
 	
 	/**
	* 
	* Get pickup data for a SmartSend delivery point
	* @return array
	*/	
	public function getPickupDataSmartsend() {
	
		$carrier = $this->getShippingCarrier();
		switch ($carrier) {
			case 'postdanmark':
				$pickupModel = Mage::getModel('logistics/postdanmark');
				break;
			case 'posten':
				$pickupModel = Mage::getModel('logistics/posten');
				break;
			case 'gls':
				$pickupModel = Mage::getModel('logistics/gls');
				break;
			case 'bring':
				$pickupModel = Mage::getModel('logistics/bring');
				break;
			default:
				throw new Exception( $this->_errors[2302] );
		}

		$order_id = $this->_order->getId();	//order id
		$pickupData = $pickupModel->getCollection()->addFieldToFilter('order_id', $order_id)->getFirstItem();        //pickup data 

		if ($pickupData->getData()) {
		
			return array(
				'id' 		=> $pickupData->getPickUpId()."-".time()."-".rand(9999,10000),
				'agentno'	=> $pickupData->getPickUpId(),
				'agenttype'	=> ($this->getShippingCarrier() == 'postdanmark' ? 'PDK' : null),
				'company' 	=> $pickupData->getCompany(),
				'name1' 	=> null,
				'name2' 	=> null,
				'address1'	=> $pickupData->getStreet(),
				'address2' 	=> null,
				'city'		=> $pickupData->getCity(),
				'zip'		=> $pickupData->getZip(),
				'country'	=> $pickupData->getCountry(),
				'sms' 		=> null,
				'mail' 		=> null,
				);

		} else {
			return null;
		}
		
	}
	
	/**
	* 
	* Get the Flex delivery comment (where to place the parcel) fromMysql
	* @return string / null
	*/
	protected function getFlexDeliveryNoteFromMysql() {
		$flexModel = Mage::getModel('logistics/flex');
        $flexData = $flexModel->getCollection()->addFieldToFilter('order_id', $this->_order->getId())->getFirstItem();        //pickup data 
        if ($flexData->getData()) {
        	if($flexData->getFlexnote() != '') {
        		return $flexData->getFlexnote();
        	} else {
        		return null;
        	}
        } else {
			return null;
		}
	}
	
/*****************************************************************************************
 * Settings
 ****************************************************************************************/
	
	/**
	* 
	* Get the settings for Post Danmark
	* @return array
	*/
	public function getSettingsPostdanmark() {
		
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/notesms'),
			'prenote'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote'),
			'prenote_from'		=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote_sender'),
			'prenote_receiver'	=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote_receiver'),
			'prenote_message'	=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote_message'),
			'flex'				=> null,
			'format'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/format'),
			'quickid'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/quickid'),
			'waybillid'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/waybillid'),
			'return'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/return'),
			);
			
	}
	
	/**
	* 
	* Get the settings for Posten
	* @return array
	*/
	public function getSettingsPosten() {
	
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendposten/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendposten/notesms'),
			'prenote'			=> Mage::getStoreConfig('carriers/smartsendposten/prenote'),
			'prenote_from'		=> Mage::getStoreConfig('carriers/smartsendposten/prenote_sender'),
			'prenote_receiver'	=> Mage::getStoreConfig('carriers/smartsendposten/prenote_receiver'),
			'prenote_message'	=> Mage::getStoreConfig('carriers/smartsendposten/prenote_message'),
			'flex'				=> null,
			'format'			=> Mage::getStoreConfig('carriers/smartsendposten/format'),
			'quickid'			=> Mage::getStoreConfig('carriers/smartsendposten/quickid'),
			'waybillid'			=> Mage::getStoreConfig('carriers/smartsendposten/waybillid'),
			'return'			=> Mage::getStoreConfig('carriers/smartsendposten/return'),
			);
			
	}
	
	/**
	* 
	* Get the settings for GLS
	* @return array
	*/
	public function getSettingsGls() {
	
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendgls/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendgls/notesms'),
			'prenote'			=> null,
			'prenote_from'		=> null,
			'prenote_receiver'	=> null,
			'prenote_message'	=> null,
			'flex'				=> null,
			'format'			=> null,
			'quickid'			=> null,
			'waybillid'			=> null,
			'return'			=> Mage::getStoreConfig('carriers/smartsendgls/return'),
			);
			
	}
	
	/**
	* 
	* Get the settings for Bring
	* @return array
	*/
	public function getSettingsBring() {
	
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendbring/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendbring/notesms'),
			'prenote'			=> null,
			'prenote_from'		=> null,
			'prenote_receiver'	=> null,
			'prenote_message'	=> null,
			'flex'				=> null,
			'format'			=> null,
			'quickid'			=> null,
			'waybillid'			=> null,
			'return'			=> Mage::getStoreConfig('carriers/smartsendbring/return'),
			);
			
	}
	
	/**
	* 
	* Should the order comment be included as freetext on label
	*
	* @return boolean
	*/
 	protected function getSettingIncludeOrderComment() {
 		return Mage::getStoreConfig('carriers/smartsend/includeordercomment');
 	}
	
/*****************************************************************************************
 * Shipments
 ****************************************************************************************/

	/**
	* 
	* Get the shipments for the order if any
	* @return array
	*/
	public function getShipments() {

		if( $this->_order->hasShipments() ) {
			return $this->_order->getShipmentsCollection();
		} else {
			return null;
		}
	}

	/**
	* 
	* Get the Track&Trace code for a given shipment
	* @return string
	*/
	public function getShipmentTrace($shipment) {

		$tracknums = array();
		foreach($shipment->getAllTracks() as $tracknum) {
			$tracknums[]=$tracknum->getNumber();
		}

		if(empty($tracknums)) {
			return false;
		} else {
			return true;
		}
			
	}
	
	/**
	* 
	* Get the weight (in kg) of a given shipment
	* @return float
	*/
	public function getShipmentWeight($shipment) {
	
		$weight = 0;
		foreach($shipment->getAllItems() as $eachShipmentItem) {
			$itemWeight = $eachShipmentItem->getWeight();
			$itemQty    = $eachShipmentItem->getQty();
			$rowWeight  = $itemWeight*$itemQty;

			$weight = $weight + $rowWeight;
		}
		
		if(isset($weight) && $weight > 0) {
			return $weight;
		} else {
			return null;
		}
	}
	
	/**
	* 
	* Get the unshipped items of the order
	* @return array
	*/
	protected function getUnshippedItems() {
	
		$items = array();
		foreach($this->_order->getAllItems() as $eachOrderItem){
			$Itemqty = 0;
			$Itemqty = $eachOrderItem->getQtyOrdered()
					- $eachOrderItem->getQtyShipped()
					- $eachOrderItem->getQtyRefunded()
					- $eachOrderItem->getQtyCanceled();
			if($Itemqty > 0) {
				$items[$eachOrderItem->getId()] = $Itemqty;
			}
		}

		if(!empty($items)) {
			return $items;
		} else {
			return null;
		}

	}
	
	/**
	* 
	* Create a parcel containing all unshipped items.
	* Add the parcel to the request.
	*/
	protected function createShipment() {

		$order = $this->_order;
		$qty = $this->getUnshippedItems();

		/* check order shipment is prossiable or not */

		$email = false;
		$includeComment = false;
		$comment = "";

		if ($order->canShip()) {
			// @var $shipment Mage_Sales_Model_Order_Shipment
			// prepare to create shipment
			$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($qty);
			if ($shipment) {
				$shipment->register();
				
				//Add a comment. Second parameter is whether or not to email the comment to the user
				$shipment->addComment($comment, $email && $includeComment);
				
				// Set the order status as 'Processing'
				$order->setIsInProcess($email);
				$order->addStatusHistoryComment(Mage::helper('logistics')->__('Shipment generated by Smart Send Logistics'), false);
				
				try {
					$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($shipment)
							->addObject($order)
							->save();
					
					//Email the customer that the order is sent
					$shipment->sendEmail($email, ($includeComment ? $comment : ''));
					
					//Set order status as complete
					//$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
					//$order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
					//$order->save();
					
					//var_dump($qty); exit();
				} catch (Mage_Core_Exception $e) {
					throw new Exception($this->_errors[2402].': '.$e);
				}
			}
		}

		if ($shipment) {
			//Lastly add the shipment to the order array.
			$this->addShipment($shipment);
		}

	}
	
	/**
	* 
	* Add a shipment to the request
	*/
	protected function addShipment($shipment) {
	
		$parcel = array(
			'shipdate'	=> null,
			'reference' => $shipment->getId(),
			'weight'	=> $this->getShipmentWeight($shipment),
			'height'	=> null,
			'width'		=> null,
			'length'	=> null,
			'size'		=> null,
			'freetext1'	=> $this->getFreetext(),
			'items' 	=> array(),
			);

		$ordered_items = $shipment->getAllItems();	
		foreach($ordered_items as $item) {
			$parcel['items'][] = $this->addItem($item);
		}
	
		$this->_parcels[] = $parcel;

	}

	/**
	* 
	* Format an item to be added to a parcel
	* @return array
	*/
	protected function addItem($item) {

		return array(
			'sku'		=> $item->getSku(),
			'title'		=> $item->getName(),
			'quantity'	=> $item->getQty(),
			'unitweight'=> $item->getWeight(),
			'unitprice'	=> $item->getPrice(),
			'currency'	=> Mage::app()->getStore()->getCurrentCurrencyCode()
			);
		  //  $item->getItemId(); //product id
	}

}