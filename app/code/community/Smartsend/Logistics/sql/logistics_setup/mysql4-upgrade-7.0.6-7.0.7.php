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
 * @folder		/app/code/community/Smartsend/Logistics/sql/logistics_setup/mysql4-upgrade-7.0.6-7.0.7.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
$installer = $this;

$installer->startSetup();            //start setup

$installer->run("
	CREATE TABLE IF NOT EXISTS {$this->getTable('smartsend_flexdelivery')} (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `order_id` int(11) NOT NULL,
	  `store` varchar(255) NOT NULL default '',
	  `flexnote` varchar(255) NOT NULL default '',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");                       //creating table


$installer->endSetup();        //end setup
