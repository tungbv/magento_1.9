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
 * @folder		/app/code/community/Smartsend/Logistics/sql/logistics_setup/mysql4-install-7.0.1.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
$installer = $this;
Mage::log('Starting installation');

$installer->startSetup();            //start setup

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_pickup')};               
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_postdanmark')};
	DROP TABLE IF EXISTS {$this->getTable('order_shipping_posten')};
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_bring')};
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_gls')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('smartsend_pickup')} (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `order_id` int(11) NOT NULL,
	  `store` varchar(255) NOT NULL default '',
          `pick_up_id` varchar(255) NOT NULL default '',
          `company` varchar(255) NOT NULL default '',
          `street` varchar(255) NOT NULL default '',
          `city` varchar(255) NOT NULL default '',
          `zip` varchar(255) NOT NULL default '',
          `country` varchar(255) NOT NULL default '',
          `carrier` varchar(255) NOT NULL default '',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");                       //creating table

/* standard values for the table rate */

$install_shipping_methods = array(
	"posten"	=> array(
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 40,
			'countries'				=> 'SE',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 0,
			'countries'				=> 'SE',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 50,
			'countries'				=> 'SE',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 10,
			'countries'				=> 'SE',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			)
		),
	"postdanmark"	=> array(
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 40,
			'countries'				=> 'DK',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 0,
			'countries'				=> 'DK',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 50,
			'countries'				=> 'DK',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 10,
			'countries'				=> 'DK',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 90,
			'countries'				=> 'SE,NO,FI',
			'methods'				=> 'private',
			'method_name'			=> Mage::helper('logistics')->__('Private'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 50,
			'countries'				=> 'SE,NO,FI',
			'methods'				=> 'private',
			'method_name'			=> Mage::helper('logistics')->__('Private'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 3,
			'pickupshippingfee'		=> 300,
			'countries'				=> 'FO,GL',
			'methods'				=> 'private',
			'method_name'			=> Mage::helper('logistics')->__('Private'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 3,
			'ordermaxweight'		=> 10,
			'pickupshippingfee'		=> 400,
			'countries'				=> 'FO,GL',
			'methods'				=> 'private',
			'method_name'			=> Mage::helper('logistics')->__('Private'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 10,
			'ordermaxweight'		=> 20,
			'pickupshippingfee'		=> 500,
			'countries'				=> 'FO,GL',
			'methods'				=> 'private',
			'method_name'			=> Mage::helper('logistics')->__('Private'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			)
		),
	"gls"	=> array(
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 40,
			'countries'				=> 'DK',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 0,
			'countries'				=> 'DK',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 50,
			'countries'				=> 'DK',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 10,
			'countries'				=> 'DK',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			)
		),
	"bring"	=> array(
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 40,
			'countries'				=> 'DK',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 0,
			'countries'				=> 'DK',
			'methods'				=> 'pickup',
			'method_name'			=> Mage::helper('logistics')->__('Pickuppoint'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 0,
			'ordermaxprice'			=> 500,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 50,
			'countries'				=> 'DK',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			),
		array(
			'orderminprice'			=> 500,
			'ordermaxprice'			=> 100000,
			'orderminweight'		=> 0,
			'ordermaxweight'		=> 100000,
			'pickupshippingfee'		=> 10,
			'countries'				=> 'DK',
			'methods'				=> 'privatehome',
			'method_name'			=> Mage::helper('logistics')->__('Private to home'),
			'_id'					=> '_' . number_format(microtime(true),0,'.','') . '_' . rand(100, 999)
			)
		)
	);	
		

for ($i = 1; $i < 5; $i++) {

    switch ($i) {                      //shipping method case
        case 1:
            $carrier = "postdanmark";
            $path = 'carriers/smartsendpostdanmark/price';
            $shipping_methods = $install_shipping_methods['postdanmark'];
            break;
        case 2:
            $carrier = "bring";
            $path = 'carriers/smartsendbring/price';
            $shipping_methods = $install_shipping_methods['bring'];
            break;
        case 3:
            $carrier = "gls";
            $path = 'carriers/smartsendgls/price';
            $shipping_methods = $install_shipping_methods['gls'];
            break;
        case 4:
            $carrier = "posten";
            $path = 'carriers/smartsendposten/price';
            $shipping_methods = $install_shipping_methods['posten'];
            break;
    }

	$ettings = "groups[smartsend".$carrier."]";

    $shipping_methods = $install_shipping_methods[$carrier];

	$priceResult = array();
    foreach ($shipping_methods as $shipping_method) {

		$priceResult[$shipping_method['_id']] = $shipping_method;

	}

	$data = array();
	$data['value'] = serialize($priceResult);
	$data['path'] = $path;
	$data['scope'] = 'default';
	$data['scope_id'] = 0;

	$table = Mage::getSingleton('core/resource')->getTableName('core_config_data');    //checking the table rate values in the table
	$writeAdapter = Mage::getSingleton('core/resource')->getConnection('core_write');
	$readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
	$query = 'SELECT * FROM ' . $table . ' where path = "' . $data["path"] . '"';
	$results = $readAdapter->fetchAll($query);

    if (!$results && !empty($priceResult)) {              //if table rate value is empty for that carrier
        $writeAdapter->insert($table, $data);
    }
}
$installer->endSetup();        //end setup
