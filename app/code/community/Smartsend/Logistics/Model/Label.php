<?php
 
/**
* Label class
*
* The label class is used to handle requests and responses from the Smart Send Logistics API
*
* @folder		/app/code/community/Smartsend/Logistics/Model/Label.php
* @category		Smart Send
* @package		Smartsend_Logistics
* @class 		Smartsend_Logistics_Label
* @copyright	Copyright (c) Smart Send ApS (http://www.smartsend.dk)
* @license		http://smartsend.dk/license
* @version		Release: 7.1.0rc1
* @author 		Smart Send ApS
* @link			http://smartsend.dk/download/generec
* @since		Class available since Release 7.1.0
*/

/*

	$label = new Smartsend_Logistics_Label($single=false);
	$label->setRequestType('bulk');
	foreach($orders as $order) {
		try{
			$label->addOrderToRequest($order);
		}
		//catch exception
		catch(Exception $e) {
			$this->addErrorMessage( $e->getMessage() );
		}
	}
	
	if( $label->hasRequestOrders() ) {
		$label->sendRequest();
		
		if( $label->getResponseError() ) {
			$label->addErrorMessage( $label->getResponseError() );
		} else {
			$label->handleApiReponse();
		}
	}
	
	$label->showResult();

*/

class Smartsend_Logistics_Model_Label extends Mage_Core_Model_Abstract {

	private $_test = false;
	
	protected $request=array();
	protected $request_type;
	protected $response;
	protected $response_type;
	protected $response_code;
	
	protected $message_string_array;
	
	protected $messages=array();
	
	protected $show_order_succes = true;
	
	public function __construct() {
	}
	
	/**
     * This method returns the message string based on message number
     *
	 * @return string
	 */
	protected function getMessageString($message_number) {
		if(isset($this->message_string_array[$message_number])) {
			return $this->message_string_array[$message_number];
		} else {
			return '';
		}
	}

	/**
     * This method returns the request to be used in the API call
     *
	 * @return string
	 */
	private function getTest() {
		return $this->_test;
	}
	
	/**
     * This method returns the messages that has been created
     *
	 * @return array
	 */
	protected function getMessages() {
		return $this->messages;
	}
	
	/**
     * This method returns the request to be used in the API call
     *
	 * @return array
	 */
	protected function getRequest() {
		return $this->request;
	}
	
	/**
     * This method returns the JSON encoded request to be used in the API call
     *
	 * @return string
	 */
	protected function getRequestJsonEncoded() {
		return json_encode($this->request);
	}
	
	/**
     * This method returns the request type to be used in the API call
     *
	 * @return array
	 */
	protected function getRequestType() {
		return $this->request_type;
	}
	
	/**
     * This method returns the response from the API call decoded from JSON/XML
     *
	 * @return string
	 */
	protected function getResponse() {
		return $this->response;
	}
	
	/**
     * This method returns the JSON decoded response from the API call
     *
	 * @return string
	 */
	protected function getResponseJsonDecoded() {
		if(strpos($this->response_type,'json') !== false) {
 			return $json = json_decode( $this->getResponse(),true );
 		}
	}
	
	/**
     * This method returns whether or not to show the individual order succes
     *
	 * @return boolean
	 */
	protected function getShowOrderSucces() {
		return $this->show_order_succes;
	}
	
	
	/**
     * This method returns the Apikey used for API calls
     *
	 * @return string
	 */
	protected function getApikey() {
		return $this->apikey;
	}
	
	/**
     * This method returns the Smart Send licensekey entered in the modules settings
     *
	 * @return string
	 */
	protected function getSmartsendLicensekey() {
		return $this->smartsend_licensekey;
	}
	
	/**
     * This method returns the Smart Send username entered in the modules settings
     *
	 * @return string
	 */
	protected function getSmartsendUsername() {
		return $this->smartsend_username;
	}
	
	/**
     * This method returns the version number of the CMS
     *
	 * @return string
	 */
	public function getCmsSystem() {
		return $this->cms_system;
	}
	
	/**
     * This method returns the version number of the CMS
     *
	 * @return string
	 */
	public function getCmsVersion() {
		return $this->cms_version;
	}
	
	/**
     * This method returns the version number of the Smart Send Logistics module
     *
	 * @return string
	 */
	public function getModuleVersion() {
		return $this->module_version;
	}
	
	/**
     * This method returns the language used my the active admin user
     *
	 * @return string
	 */
	public function getCmsLanguage() {
		return str_replace("_","-",$this->cms_language);
	}
	
	/**
     * This method returns the module settings
     *
	 * @return array
	 */
	public function getSettings() {
		return $this->settings;
	}
	
	/**
	 * This method is used to check if any orders has been added to the request
	 *
	 * @return bolean
	 */
	 public function hasRequestOrders() {
	 	$request = $this->getRequest();
	 	if( is_array($request) && !empty($request) ) {
	 		return true;
	 	} else {
	 		return false;
	 	}
	 }
	 
	 /**
     * This method sets the request type to be used for the API call
     *
	 * @return void
	 */
	public function setRequestType($request_type) {
		$this->request_type = $request_type;
	}
	
	/**
     * This method sets the response from the API call
     *
	 * @return void
	 */
	private function setResponse($response) {
		$this->response = $response;
	}
	
	/**
     * This method sets the response type from the API call
     *
	 * @return void
	 */
	private function setResponseType($response_type) {
		$this->response_type = $response_type;
	}
	
	/**
     * This method sets the response code from the API call
     *
	 * @return void
	 */
	private function setResponseCode($response_code) {
		$this->response_code = $response_code;
	}
	
	public function setShowOrderSucces($boolean) {
		$this->show_order_succes = $boolean;
	}
	
	/**
     * This method performs the API request
     *
	 * @return void
	 */
	public function sendRequest() {
		
		//intitiate curl
		$ch = curl_init();

        /* API URL */
        switch ($this->getRequestType()) {
			case 'bulk':
				//Label was created from order list
				$url = 'http://smartsend-prod.apigee.net/v7/booking/orders';
				break;
			case 'single':
				//Label was created from order info page
				$url = 'http://smartsend-prod.apigee.net/v7/booking/order';
				break;
			default:
				throw new Exception( $this->getMessageString(2201) );
		}
        
        // Check if reqest is empty
        if( empty($this->getRequest()) ) {
        	throw new Exception( $this->getMessageString(2202) );
        }
        
        // Check if there is missing settings:
        if($this->getSmartsendUsername() == '') {
        	throw new Exception( $this->getMessageString(2212) );
        } elseif($this->getSmartsendLicensekey() == '') {
        	throw new Exception( $this->getMessageString(2213) );
        }

        curl_setopt($ch, CURLOPT_URL, $url);       //curl url
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getRequestJsonEncoded());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        	'apikey:'.$this->getApikey(),
        	'smartsendmail:'.$this->getSmartsendUsername(),
        	'smartsendlicence:'.$this->getSmartsendLicensekey(),
        	'cmssystem:'.$this->getCmsSystem(),
        	'cmsversion:'.$this->getCmsVersion(),
        	'appversion:'.$this->getModuleVersion(),
        	'test:'.($this->getTest() ? 'true' : 'false'),
        	'Content-Type:application/json; charset=UTF-8',
        	"Accept: text/xml",
        	'Accept-Language:'.$this->getCmsLanguage(),
        	));    //curl request header
        
        $response = curl_exec($ch); //executes the request
        $response_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response_error = curl_error($ch);
        // cURL error code: curl_errno($ch)
    
        // Set the respinse
        $this->setResponse( $response );
        $this->setResponseType( $response_type );
        $this->setResponseCode( $response_error );
        //meta data: curl_getinfo($ch);
    
        // If there were any 
        if( !($response_code >= 200 &&  $response_code < 300) ) {
            throw new Exception( $this->getMessageString(2203) . ': (' . $response_code . ') '. $response );
        }
        
        curl_close($ch); // Close the curl. Only AFTER the errro has been checked

	}
	
	/**
     * This method handles the response from the API request
     *
	 * @return void
	 */
	public function handleApiReponse() {
	
		$response = $this->getResponseJsonDecoded();
		
		$settings = $this->getSettings();
		
		// Add notification if the info-field is set in the response
		if( isset($response['info']) && $response['info'] != '') {
			if(is_array($response['info'])) {
				// Add an array of info messages
				foreach($response['info'] as $info_message) {
					$this->addWarningMessage( $info_message );
				}
			} else {
				// Add an info message
				$this->addWarningMessage( $response['info'] );
			}
		}
		
		if( !(isset($response['combine_pdf']) && $response['combine_pdf'] != '') && !(isset($order_response['combine_link']) && $order_response['combine_link'] != '') ){
			$this->setShowOrderSucces(true);
		} else {
			$this->setShowOrderSucces(!$settings['combine_pdf_labels']);
		}
		
		if(is_array($response['orders'])) {
			// An array of orders is returned from the API
			foreach($response['orders'] as $order) {
				try {
					$this->handleOrderResponse($order);
				}
				//catch exception
				catch(Exception $e) {
					$this->addErrorMessage( $this->getMessageString(2000) . ' ' . $order['reference'] . ': ' . $e->getMessage() );
				}
			}
			
			if( $settings['combine_pdf_labels'] ) {
				// Show the combined link or PDF link
				if(isset($response['combine_pdf']) && $response['combine_pdf'] != '') {
					// Add link to PDF label
					$this->addSuccessMessage('<a href="' . $response['combine_pdf'] . '" target="_blank">' . $this->getMessageString(2101) . '</a>' );
				} elseif(isset($order_response['combine_link']) && $order_response['combine_link'] != '') {
					// Add link to label
					$this->addSuccessMessage('<a href="' . $response['combine_link'] . '" target="_blank">' . $this->getMessageString(2102) . '</a>' );
				}
			}
		} elseif(isset($response['orderno'])) {
			$this->handleOrderResponse($response);
		} else {
			// No orders was returned from the API
			if(isset($response['message']) && $response['message'] != '') {
				$this->addWarningMessage( $response['message'] );
			} else {
				$this->addWarningMessage( $this->getMessageString(2204) );
			}
		}
	}
	
	/**
     * This method handles each order response from the API request
     *
     * @param array $order_response contains the API response, JSON devoced to an array
     *
	 * @return void
	 */
	public function handleOrderResponse($order_response) {
	
		$order = $this->loadOrderModel($order_response['orderno']);
		$smartsendorder = $this->loadSmartsendOrderModel();
 		$smartsendorder->setOrderObject($order);
 		
 		if( $smartsendorder->getOrderId() ) {
 		
 			$order_comment = $this->getMessageString(2105);
 			if(isset($order_response['pdflink'])) {
 				$order_comment .= '<br><a href="' . $order_response['pdflink'] . '" target="_blank">' . $this->getMessageString(2103) . '</a>';
 			}
			if(is_array($order_response['parcels'])) {
				// This array will be used to send shipment emails
				$parcels_succes_array = array();
				
				// An array of orders is returned from the API
				foreach($order_response['parcels'] as $parcel) {
					try {
						$order_comment_array = $this->handleParcelResponse($order_response,$parcel);
						$parcels_succes_array[] = $order_comment_array;
						if(isset($order_comment_array['tracecode']) && isset($order_comment_array['tracelink'])) {
							$order_comment .= '<br>' . $this->getMessageString(2106) . ': <a href="' . $order_comment_array['tracelink'] . '" target="_blank">' . $order_comment_array['tracecode'] . '</a>';
						}
					}
					//catch exception
					catch(Exception $e) {
						$this->addErrorMessage( $this->getMessageString(2000) . ' ' . $order_response['reference'] . ': ' . $e->getMessage() );
					}
				}
				
				$settings = $this->getSettings();
				
				try {
					// Add comment to order
					$this->addCommentToOrder($order_response['orderno'],$order_comment);
				
					// Send email with shipping information
					if($settings['send_shipment_mail']) {
						$this->sendShipmentEmail($order_response['orderno'],$parcels_succes_array,$customer_email_comments=null);
					}
				
					// Change order status
					if($settings['change_order_status']) {
						$this->setOrderStatus($order_response['orderno'],$settings['change_order_status']);
					}
				//catch exception
				} catch(Exception $e) {
					$this->addErrorMessage( $this->getMessageString(2000) . ' ' . $order_response['reference'] . ': ' . $e->getMessage() );
				}
				
				// Show succes message with label link or pdf link
				if($this->getShowOrderSucces()) {
					if(isset($order_response['pdflink']) && $order_response['pdflink'] != '') {
						// Add link to PDF label
						$this->addSuccessMessage( $this->getMessageString(2000) . ' '. $smartsendorder->getOrderReference() .': <a href="' . $order_response['pdflink'] . '" target="_blank">' . $this->getMessageString(2103) . '</a>' );
					} elseif(isset($order_response['link']) && $order_response['link'] != '') {
						// Add link to label
						$this->addSuccessMessage( $this->getMessageString(2000) . ' '. $smartsendorder->getOrderReference() .': <a href="' . $order_response['link'] . '" target="_blank">' . $this->getMessageString(2104) . '</a>' );
					} else {
						 throw new Exception( $this->getMessageString(2207) );
					}
				}
				
			} else {
				// No parcels was returned from the API
				if(isset($order_response['message']) && $order_response['message'] != '') {
					$this->addErrorMessage( $this->getMessageString(2000) . ' ' . $order_response['reference'] . ': ' . $order_response['message'] );
				} else {
					$this->addErrorMessage( $this->getMessageString(2000) . ' ' . $order_response['reference'] . ': ' . $this->getMessageString(2205) );
				}
			}
			
		} else {
			// Unknwn order
			$this->addErrorMessage( $this->getMessageString(2206) );
		}
	}
	
	/**
     * This method handles each parcel response from the API request
     *
     * @param
     *
	 * @return array
	 */
	public function handleParcelResponse($order_response,$parcel_response) {
		if(isset($parcel_response['tracecode']) && $parcel_response['tracecode'] != '') {
			$this->addTracecodeToParcel($order_response['orderno'], $parcel_response['reference'], $order_response['carrier'], $parcel_response['tracecode'],$parcel_response['tracelink']);
				return array(
					'id'		=> $parcel_response['id'],
					'reference'	=> $parcel_response['reference'],
					'tracecode'	=> $parcel_response['tracecode'],
					'tracelink'	=> $parcel_response['tracelink']
					);
		} else {
			return;
		}
		
	}
	
	/*
	 * Add an error message that is subsequently shown in admin
	 *
	 * @param string $error_message
	 *
	 * @return void
	 */
	public function addErrorMessage($error_message) {
		$this->messages[] = array(
			'type'		=> 'error',
			'text'		=> $error_message
			);
	}
	
	/*
	 * Add a warning message that is subsequently shown in admin
	 *
	 * @param string $warning_message
	 *
	 * @return void
	 */
	public function addWarningMessage($warning_message) {
		$this->messages[] = array(
			'type'		=> 'warning',
			'text'		=> $warning_message
			);
	}
	
	/*
	 * Add a success message that is subsequently shown in admin
	 *
	 * @param string $success_message
	 *
	 * @return void
	 */
	public function addSuccessMessage($success_message) {
		$this->messages[] = array(
			'type'		=> 'success',
			'text'		=> $success_message
			);
	}
	
	/*
	 * Add an information message that is subsequently shown in admin
	 *
	 * @param string $information_message
	 *
	 * @return void
	 */
	public function addInfoMessage($information_message) {
		$this->messages[] = array(
			'type'		=> 'info',
			'text'		=> $information_message
			);
	}
	
}