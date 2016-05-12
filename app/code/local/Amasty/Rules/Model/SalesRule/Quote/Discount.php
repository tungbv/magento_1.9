<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Model_SalesRule_Quote_Discount extends Mage_SalesRule_Model_Quote_Discount
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add discount total information to address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Amasty_Rules_Model_SalesRule_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (!Mage::getStoreConfig('amrules/general/breakdown'))
            return parent::fetch($address);
        
        $amount = $address->getDiscountAmount();
        if ($amount != 0) {
            $address->addTotal(array(
                'code'      => $this->getCode(),
                'title'     => Mage::helper('sales')->__('Discount'),
                'value'     => $amount,
                'full_info' => $address->getFullDescr(),
            ));
        }
        return $this;
    }
}