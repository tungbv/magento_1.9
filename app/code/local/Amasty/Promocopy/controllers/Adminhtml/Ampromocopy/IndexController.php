<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Promocopy
 */
class Amasty_Promocopy_Adminhtml_Ampromocopy_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected $gridUrl = 'adminhtml/promo_quote/index';
   
    public function indexAction()
    {
        return $this->_fault($this->__('Please select some action.'));        
    }
    
    public function duplicateAction()
    {
        $id = $this->getRequest()->getParam('rule_id');
        if (!$id) {
            return $this->_fault($this->__('Please select a rule to duplicate.'));
        }
        
        try {
            $mainRule = Mage::getSingleton('salesrule/rule')->load($id);
            if (!$mainRule->getId()){
                return $this->_fault($this->__('Please select a rule to duplicate.'));
            }
            
            //just pre-load values
            $mainRule->getStoreLabels();
            
            // a proper clone function has not been implemented 
            // for the rule class, so we need to unlink coupon object manually
            $rule = clone $mainRule;
            $oldCoupon = $rule->acquireCoupon();
            if ($oldCoupon){
                $oldCoupon->setId(0);
            }
            
            // set default data
            $rule->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON);
            $rule->setIsActive(0);
            
            //create new acually
            $rule->setId(null);
            $rule->save();
            
            $this->_getSession()->addSuccess(
                $this->__('The rule has been duplicated. Set a new coupon and activate it if needed.')
            );
            return $this->_redirect('adminhtml/promo_quote/edit', array('id' => $rule->getId()));            
        } 
        catch (Exception $e) {
            return $this->_fault($e->getMessage());
        } 
        
        //unreachable 
        return $this;  
    }       
    
    public function moveUpAction()
    {
        return $this->_move('up');
    }
    
    public function moveDownAction()
    {
        return $this->_move('doen');
    }
    
    protected function _move($direction)
    {
        $ids = $this->getRequest()->getParam('rules');
        if (!is_array($ids)) {
            return $this->_fault($this->__('Please select rule(s).'));
        }
        $num = 0;
        
        $db     = Mage::getSingleton('core/resource')->getConnection('core_write');  
        $table  = Mage::getSingleton('core/resource')->getTableName('salesrule/rule');        
        foreach ($ids as $id) {
            try {
                $select = $db->select()->from($table)->where('rule_id = ?', $id)->limit(1);
                $row = $db->fetchRow($select);
                if (!$row){
                    $this->_fault($this->__('Can not find rule with id=%s.', $id));
                    continue;
                }
                   
                if ('up' == $direction){ // move up
                    $select = $db->select()
                        ->from($table, array(new Zend_Db_Expr('MIN(sort_order)')))
                        ->where('sort_order <= ? ', $row['sort_order'])
                        ->where('rule_id != ?', $row['rule_id']);
                    $minPos = $db->fetchOne($select); 
                    
                    if (is_null($minPos)) // it is already the first item
                        continue;
                    
                    if ($minPos == 0){
                        $db->update($table, array('sort_order' => new Zend_Db_Expr('sort_order+1')));
                        $minPos=1;
                    }
                    if ($row['sort_order'] >= $minPos){
                        $db->update($table, array('sort_order'=>$minPos-1), 
                            'rule_id =' . intval($row['rule_id']));  
                         ++$num;   
                    }                    
                } 
                else { // move down
                    $select = $db->select()
                        ->from($table, array(new Zend_Db_Expr('MAX(sort_order)')))
                        ->where('rule_id != ?', $row['rule_id']);
                    $maxPos = $db->fetchOne($select);  
                    
                    if (is_null($maxPos)) // it is already the last item
                        continue;                    
                    
                    if ($maxPos >= 4294967295)  { // I'm paranoic :)
                        $this->_fault($this->__('Can not move rule with id=%s.', $id));
                        continue;
                    }  
                    if ($row['sort_order'] <= $maxPos){  
                        $db->update($table, array('sort_order'=>$maxPos+1), 
                            'rule_id =' . intval($row['rule_id']));
                        ++$num;    
                    }             
                }
                
            } 
            catch (Exception $e) {
                $this->_fault($e->getMessage(), false);
            }   
        }
        return $this->_success($this->__('Total of %d rule(s) have been moved.', $num));
    }    
    
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('rules');
        if (!is_array($ids)) {
            return $this->_fault($this->__('Please select rule(s).'));
        }
        
        $num = 0;
        foreach ($ids as $id) {
            try {
                $rule = Mage::getSingleton('salesrule/rule')->load($id);
                $rule->getPrimaryCoupon()->delete();
                $rule->delete();
                ++$num;
            } 
            catch (Exception $e) {
                $this->_fault($e->getMessage(), false);
            }   
        }
        return $this->_success($this->__('Total of %d record(s) have been deleted.', $num));
    }

    public function massEnableAction()
    {
        return $this->_modifyStatus(1);
    }
    
    public function massDisableAction()
    {
        return $this->_modifyStatus(0);
    }         
      
    protected function _modifyStatus($status)
    {
        $ids = $this->getRequest()->getParam('rules');
        if (!is_array($ids)) {
            return $this->_fault($this->__('Please select rule(s).'));
        }
        
        $num = 0;
        foreach ($ids as $id) {
            try {
                $rule = Mage::getModel('salesrule/rule')->load($id);
                if ($rule->getIsActive() != $status){
                    $rule->setIsActive($status);
                    $rule->save();
                    ++$num;
                }
            } 
            catch (Exception $e) {
                $this->_fault($e->getMessage(), false);
            }   
        }
        return $this->_success($this->__('Total of %d record(s) have been updated.', $num));
    }    
      
    protected function _fault($message, $redirect=true)
    {
        $this->_getSession()->addError($message);
        if ($redirect)
            $this->_redirect($this->gridUrl);
            
        return $this;
    }
    
    protected function _success($message, $redirect=true)
    {
        $this->_getSession()->addSuccess($message);
        if ($redirect)
            $this->_redirect($this->gridUrl);
            
        return $this;
    }

    /**
     * Returns result of current user permission check on resource and privilege
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/quote');
    }
}