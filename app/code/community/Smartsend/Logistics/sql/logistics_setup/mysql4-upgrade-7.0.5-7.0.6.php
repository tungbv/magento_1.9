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
 * @folder		/app/code/community/Smartsend/Logistics/sql/logistics_setup/mysql4-upgrade-7.0.5-7.0.6.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
/* Transactional Email for smartsend shipping */

$table 			= Mage::getSingleton('core/resource')->getTableName('core_email_template');    //checking the table 
$writeAdapter 	= Mage::getSingleton('core/resource')->getConnection('core_write');
$readAdapter 	= Mage::getSingleton('core/resource')->getConnection('core_read');


$template  		= Mage::getModel('adminhtml/email_template');
$templateCode 	= "smartsend_new_shipment_email";

if (!Mage::registry('email_template')) {
	Mage::register('email_template', $template);
}
if (!Mage::registry('current_email_template')) {
	Mage::register('current_email_template', $template);
}

$template->loadDefault($templateCode, 'da_DK');
$template->setData('orig_template_code', $templateCode);
$template->setData('template_variables', Zend_Json::encode($template->getVariablesOptionArray(true)));

if (!Mage::registry('email_template')) {
	Mage::register('email_template', $model);
}
if (!Mage::registry('current_email_template')) {
	Mage::register('current_email_template', $model);
}

$templateBlock = Mage::app()->getLayout()->createBlock('adminhtml/system_email_template_edit');
$template->setData('orig_template_used_default_for', $templateBlock->getUsedDefaultForPaths(false));

$template_new  = Mage::getModel('adminhtml/email_template');
$template_new->loadByCode('smartsend_sales_email_shipment_template');
if(!$template_new->getTemplateId()){
	$template_new->setTemplateSubject($template->getData('template_subject'))
			->setTemplateCode('smartsend_sales_email_shipment_template')
			->setTemplateText($template->getData('template_text'))
			->setTemplateStyles($template->getData('template_styles'))
			->setModifiedAt(Mage::getSingleton('core/date')->gmtDate())
			->setOrigTemplateCode($template->getData('orig_template_code'))
			->setOrigTemplateVariables($template->getData('orig_template_variables'));

	if (!$template_new->getId()) {
		$template_new->setAddedAt(Mage::getSingleton('core/date')->gmtDate());
		$template_new->setTemplateType(Mage_Core_Model_Email_Template::TYPE_HTML);
	}

	$template_new->save();
}
            
