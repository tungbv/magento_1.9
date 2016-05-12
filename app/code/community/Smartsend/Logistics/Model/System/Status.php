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
 * @folder		/app/code/community/Smartsend/Logistics/Model/System/Status.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */

class Smartsend_Logistics_Model_System_Status extends Mage_Core_Model_Config_Data {

    public function toOptionArray() {                 //address list format for the admin system config

		$opt[] = array('value' => '', 'label' =>  Mage::helper('logistics')->__("Don't change order status"));

		// Create status array
		$all_status = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
		foreach($all_status as $single_status) {
			$opt[] = array('value' => $single_status["status"], 'label' => $single_status["label"]);
		}

        return $opt;
    }

}
