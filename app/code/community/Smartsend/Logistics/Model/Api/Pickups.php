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
 * @folder		/app/code/community/Smartsend/Logistics/Model/Api/Pickups.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Model_Api_Pickups extends Mage_Core_Model_Abstract {

	private $_pickupPoints;
	
	public function __construct() {
    }

    public function _post($street, $city, $postcode, $country,$carriers) {
      
        $ch = curl_init();

        /* Script URL */
        $url = 'http://smartsend-prod.apigee.net/v7/pickup/';

        /* $_GET Parameters to Send */
        $params = array('country' => $country, 'zip' => $postcode, 'city' => $city, 'street' => $street);

        /* Update URL to container Query String of Paramaters */
        $url .= $carriers . '?' . http_build_query($params);

        curl_setopt($ch, CURLOPT_URL, $url);               //curl url
        curl_setopt($ch, CURLOPT_HTTPGET, true);               //curl request method
        //curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        	'apikey:yL18TETUVQ7E9pgVb6JeV1erIYHAMcwe',
        	'smartsendmail:'.Mage::getStoreConfig('carriers/smartsend/username'),
        	'smartsendlicence:'.Mage::getStoreConfig('carriers/smartsend/licensekey'),
        	'cmssystem:Magento',
        	'cmsversion:'.Mage::getVersion(),
        	'appversion:'.Mage::getConfig()->getNode('modules/Smartsend_Logistics')->version
        	));    //curl request header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = new StdClass();
        $result->response = curl_exec($ch);                  //executing curl
        $result->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result->meta = curl_getinfo($ch);
        $curl_error = ($result->code > 0 ? null : curl_error($ch) . ' (' . curl_errno($ch) . ')');         //getting curl error if any

        curl_close($ch);               //curl closing
        
        if($curl_error == null && $result->response != '') {
			$this->_pickupPoints = json_decode($result->response, true);
		} 
	}
    
	public function getPickupPoints() {
     	
    	// decoding the pickup points data into array 
    	if( isset($this->_pickupPoints) && is_array($this->_pickupPoints) ) {                                             // if no pickup data return false
       	$servicePoints = $this->_pickupPoints;
			$format = Mage::getStoreConfig('carriers/smartsend/listformat');            //get the address format from the admin system config
			for ($i = 0; $i < count($servicePoints); $i++) {
				if (!isset($servicePoints[$i])) {
					break;
				}
				$addressData = $this->getaddressData($servicePoints[$i]);       //getting address data form the pickup points response 
				switch ($format) {
					case 1:
						$resultData[$addressData['servicePointId']] = array(
							'company' 	=> $addressData['company'],
							'street' 	=> $addressData['street'],
							'zip_code' 	=> $addressData['zipcode'],
							'city' 		=> $addressData['city']
						);
						break;
					case 2:
						$resultData[$addressData['servicePointId']] = array(
							'company' 	=> $addressData['company'],
							'street' 	=> $addressData['street'],
							'zipcode' 	=> $addressData['zipcode']
						);
						break;
					case 3:
						$resultData[$addressData['servicePointId']] = array(
							'company' 	=> $addressData['company'],
							'street' 	=> $addressData['street'],
							'city' 		=> $addressData['city']
						);
					default:
						$resultData[$addressData['servicePointId']] = array(
							'company' 	=> $addressData['company'],
							'street' 	=> $addressData['street'],
						);
						break;
				}
			}
		}
		
		if(isset($resultData) && is_array($resultData)) {
			return $resultData;
		} else {
			return false;
		}
		
		return false;
        
	}
	
	private function getaddressData($servicePoint) {
        $data['pick_up_id'] 	= $servicePoint['pickupid'];
        $data['company']		= implode(" ", array_filter(array($servicePoint['name1'], $servicePoint['name2'])));          //joining the address data 
        $data['city'] 			= $servicePoint['city'];
        $data['street'] 		= implode(" ", array_filter(array($servicePoint['address1'], $servicePoint['address2'])));
        $data['zipcode'] 		= $servicePoint['zip'];
        $data['shippingMethod'] = $servicePoint['carrier'];
        $data['country'] 		= $servicePoint['country'];
        
        $ser 					= serialize($data);
        $data['servicePointId'] = $ser;
        return $data;
    }
	
}
