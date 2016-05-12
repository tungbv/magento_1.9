<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Promocopy
 */
class Amasty_Promocopy_Block_Adminhtml_Promo_Quote_Grid extends Mage_Adminhtml_Block_Promo_Quote_Grid
{
    protected function _prepareCollection()
    {
        /** @var $collection Mage_SalesRule_Model_Mysql4_Rule_Collection */
        $collection = Mage::getModel('salesrule/rule')
            ->getResourceCollection()
        ;
        if ($collection instanceof Mage_Rule_Model_Resource_Rule_Collection_Abstract) {
            $collection->addWebsitesToResult();
        }
        $collection->getSelect()->where('name <> "AmastyXY"');
        $this->setCollection($collection);

        @Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        return $this;

    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        $positionColumn = $this->getColumn('sort_order');
        if ($positionColumn)
            $positionColumn->setWidth('20px');        
        
        $this->addColumnAfter('simple_action', array(
            'header'    => Mage::helper('salesrule')->__('Action'),
            'width'     => '120px',
            'type'      => 'options',
            'options'   => $this->getDiscountTypes(),
            'index'     => 'simple_action',
        ), 'coupon_code');
        
        $this->addColumnAfter('discount_amount', array(
            'header'    => Mage::helper('sales')->__('Discount'), //its correct
            'align'     => 'right',
            'index'     => 'discount_amount',
            'getter'     => array($this, 'formatDiscount'),
        ), 'simple_action');
        
        $this->addColumnAfter('stop_rules_processing', array(
            'header'    => $this->__('Stop'),
            'index'     => 'stop_rules_processing',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('salesrule')->__('Yes'),
                0 => Mage::helper('salesrule')->__('No'),
            ),
        ), 'to_date');  
        
        $this->addColumn('action',array(
            'header'    => Mage::helper('catalog')->__('Action'), //its correct
            'width'     => '50px',
            'type'      => 'action',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('catalog')->__('Duplicate'),
                    'url'     => array('base' => 'adminhtml/ampromocopy_index/duplicate'),
                    'field'   => 'rule_id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'rule_id',
            'is_system' => true,
        ));               
        
        $this->sortColumnsByOrder();
        
        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rules');
        
        $this
            ->addAction('Activate','massEnable')
            ->addAction('De-activate','massDisable')
            ->addAction('--- --- --- ---', 'index/1', true)
            ->addAction('Top Priority', 'moveUp')
            ->addAction('Lowest Priority',  'moveDown')
            ->addAction('--- --- --- ---', 'index/2', true)
            ->addAction('Delete', 'massDelete', true)
        ;
            
        return $this; 
    }    

    protected function addAction($label, $urlKey='', $isConfim=false)   
    {
        $this->getMassactionBlock()->addItem(str_replace('/', '_', $urlKey), array(
             'label'    => $this->__($label),
             'url'      => $this->getUrl('adminhtml/ampromocopy_index/'. $urlKey),
             'confirm'  => $isConfim ? $this->__('Are you sure?') : null,
        )); 
        
        return $this;       
    }
    
    protected function getDiscountTypes()
    {
        $options = array( // for 1.4.1 compatibility, we can't use constants
            'by_percent'  => Mage::helper('ampromocopy')->__('Price Percent'),
            'by_fixed'    => Mage::helper('ampromocopy')->__('Product Amount'),
            'cart_fixed'  => Mage::helper('ampromocopy')->__('Cart Amount'),
            'buy_x_get_y' => Mage::helper('ampromocopy')->__('Buy X get Y'),
            'ampromo_items' => Mage::helper('ampromocopy')->__('Auto add promo items with products'),
            'ampromo_cart' => Mage::helper('ampromocopy')->__('Auto add promo items for the whole cart'),
            'ampromo_product' => Mage::helper('ampromocopy')->__('Auto add the same product'),
            'ampromo_spent' => Mage::helper('ampromocopy')->__('Auto add promo items for every $X spent'),
        );
        if (Mage::helper('ambase')->isModuleActive('Amasty_Rules')){
            $newOptions = array(
                'the_cheapest'       => Mage::helper('amrules')->__('The Cheapest'),
                'the_most_expencive' => Mage::helper('amrules')->__('The Most Expencive'),
                'to_fixed'           => Mage::helper('amrules')->__('Fixed Item Price'),
                'each_n'             => Mage::helper('amrules')->__('Each N-th Free'),   
            ); 
            $rules = Mage::helper('amrules');           
            if (method_exists($rules, 'getDiscountTypes')){
                $newOptions = $rules->getDiscountTypes(true);
            } 

            $options = array_merge($options, $newOptions);
        }
        
        return $options;
    } 
        
    public function formatDiscount($row)
    {
        return number_format($row->getData('discount_amount'),2);
    }
    
}
