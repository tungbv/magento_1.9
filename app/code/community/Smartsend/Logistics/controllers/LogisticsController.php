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
 * @folder		/app/code/community/Smartsend/Logistics/controllers/LogisticsController.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     GNU General Public License v3.0
 */

class Smartsend_Logistics_LogisticsController extends Mage_Adminhtml_Controller_Action {

/***********
 * Functions called from action buttons
 ***********/
 
 
 	/*
 	 * Action: Create single order
 	 */
    public function labelNormalAction() {   //label action
    
    	$order_id = $this->getRequest()->getParam('order_id');
    	
    	$this->createLabelAction($order_id,false);
	
        $this->_redirectReferer();
        return;
    }
    
    /*
 	 * Action: Create array of orders
 	 */
    public function labelNormalMassAction() {   //label mass action
    
        $order_ids = $this->getRequest()->getPost('order_ids');
        
        $this->createLabelAction($order_ids,false);
	
        $this->_redirectReferer();
        return;
    }

	/*
 	 * Action: Create single return order
 	 */
    public function labelReturnAction() {         // return  label action
    	
    	$order_id = $this->getRequest()->getParam('order_id');
    	
    	$this->createLabelAction($order_id,true);
	
        $this->_redirectReferer();
        return;
    }
    
    /*
 	 * Action: Create array of return orders
 	 */
    public function labelReturnMassAction() {   //label return mass action
    
    	$order_ids = $this->getRequest()->getPost('order_ids');
        
        $this->createLabelAction($order_ids,true);
	
        $this->_redirectReferer();
        return;
    }
    
    /*
 	 * Action: Create single order + return
 	 */
    public function labelNormalReturnAction() {         // return  label action
    	
    	$order_id = $this->getRequest()->getParam('order_id');
    	
    	$this->createLabelAction($order_id,'both');
	
        $this->_redirectReferer();
        return;
    }
    
     /*
 	 * Action: Create array of orders + return
 	 */
    public function labelNormalReturnMassAction() {   //label + return mass action
    
    	$order_ids = $this->getRequest()->getPost('order_ids');
        
        $this->createLabelAction($order_ids,'both');
	
        $this->_redirectReferer();
        return;
    }
    
    
    
/***********
 * Functions used to generate labels
 ***********/

	/*
	 * Function to generate a label
	 *
	 * @param array|string $order_ids is an array of the id of the orders to include in the API call or just a string of a single order id
	 * @param boolean $return indicated if the label is a normal (false) or return (true) label
	 *
	 * @return void
	 *
	 */
 	protected function createLabelAction($order_ids,$return=false) {
 	
 		$label = Mage::getModel('logistics/labelmagento');
 	
 		if(is_array($order_ids) && !empty($order_ids)) {
 			$label->setRequestType('bulk');
			foreach($order_ids as $order_id) {
				$order = Mage::getModel('sales/order')->load($order_id);
				try{
					if((string)$return == 'both') {
						$label->addOrderToRequest($order,false);
						$label->addOrderToRequest($order,true);
					} else {
						$label->addOrderToRequest($order,$return);
					}
				}
				//catch exception
				catch(Exception $e) {
					$label->addErrorMessage( $this->__('Order') . ' ' . $order->getIncrementId() . ': ' . $e->getMessage() );
				}
			}
		} elseif( !empty($order_ids) ) {
			$order = Mage::getModel('sales/order')->load($order_ids);
			try{
				if((string)$return == 'both') {
					$label->setRequestType('bulk');
					$label->addOrderToRequest($order,false);
					$label->addOrderToRequest($order,true);
				} else {
					$label->setRequestType('single');
					$label->addOrderToRequest($order,$return);
				}
			}
			//catch exception
			catch(Exception $e) {
				$label->addErrorMessage( $this->__('Order') . ' ' . $order->getIncrementId() . ': ' . $e->getMessage() );
			}
		} else {
			$this->_getSession()->addError($this->__('No orders selected')); 
		}
	
		if( $label->hasRequestOrders() ) {
			try{
				$label->sendRequest();
				$label->handleApiReponse($show_individual_succes);
			} catch(Exception $e) {
				$label->addErrorMessage( $e->getMessage() );
			}
		}
	
		$label->showResult();
 	
 	}	
 	 	
}
