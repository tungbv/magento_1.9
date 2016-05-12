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
 * @folder		/app/code/community/Smartsend/Logistics/Block/Adminhtml/System/Config/Excludetax.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Block_Adminhtml_System_Config_Excludetax extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected $_values = null;

    protected function _construct() {

        $this->setTemplate('logistics/system/config/excludetax.phtml');
        return parent::_construct();
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setNamePrefix($element->getName())
                ->setHtmlId($element->getHtmlId());
        return $this->_toHtml();
    }

    public function getValues() {
        $values = array();

        $values['1'] = '';

        return $values;
    }

    public function getIsChecked($name) {

        if ($this->getCheckedValues()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getCheckedValues() {

        $data = $this->getConfigData();

        if ($data[str_replace('_', '/', $this->getHtmlId())] == '1') {
            $data = $data[str_replace('_', '/', $this->getHtmlId())];
        } else {
            $data = '';
        }

        $this->_values = $data;


        return $this->_values;
    }

}
