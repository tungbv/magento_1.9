<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_SalesRule_Rule_Condition_Product_Subselect
    extends Mage_SalesRule_Model_Rule_Condition_Product_Subselect
{
    /**
     * Validate items total amount or total qty
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getQuote()->getAllItems() as $item) {
            // fix magento bug
            if ($item->getParentItemId()){
                continue;
            }

            // for bundle we need to add a loop here
            // if we treat them as set of separate items

            $validator = new Amasty_Rules_Model_SalesRule_Rule_Condition_Product_Combine();
            $validator->setData($this->getData());
            $result = $validator->validate($item);
            $this->setData($validator->getData());

            if ($result){
                $total += $item->getData($attr);
            }
        }

        return $this->validateAttribute($total);
    }
}
