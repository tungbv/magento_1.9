<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Block_Adminhtml_Promo_Quote_Edit extends Mage_Adminhtml_Block_Promo_Quote_Edit
{
    public function __construct()
    {
        parent::__construct();

        $percent            = Amasty_Rules_Helper_Data::TYPE_XY_PERCENT;
        $fixed              = Amasty_Rules_Helper_Data::TYPE_XY_FIXED;
        $fixdisc            = Amasty_Rules_Helper_Data::TYPE_XY_FIXDISC;

        $buyxbetnfixed      = Amasty_Rules_Helper_Data::TYPE_XN_FIXED;
        $buyxbetnpercent    = Amasty_Rules_Helper_Data::TYPE_XN_PERCENT;
        $buyxbetnfixdisc    = Amasty_Rules_Helper_Data::TYPE_XN_FIXDISC;


        $setof_percent      = Amasty_Rules_Helper_Data::TYPE_SETOF_PERCENT;
        $setof_fixed        = Amasty_Rules_Helper_Data::TYPE_SETOF_FIXED;
        $each_m_perc        = Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_PERC;
        $each_m_disc        = Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_DISC;
        $each_m_fix         = Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_FIX;


        // ampromo - is correct name, it is for comatibility with Auto Add Promo Items
        $this->_formScripts[] = "
			function ampromo_note() {
                var selectNote = $('rule_simple_action_note');
                var select = $('rule_simple_action');
                if (!selectNote) {
                    select.insert({
                        after: new Element('p', {class: 'note', id: 'rule_simple_action_note'})
                    });

                    selectNote = $('rule_simple_action_note');
                }

                var noteTemplate = new Template('" . $this->__('Please see <a href="https://amasty.com/knowledge-base/special-promotions-#{value}.html">usage example</a>') . ".');
                selectNote.update( noteTemplate.evaluate({value: select.value.split('_').join('-')}) );
			}

			function ampromo_hide() {
				$('rule_discount_qty').up().up().show();
                $('rule_discount_step').up().up().show();
                $$('div.rule-tree').each(Element.show);

                var s = $('rule_apply_to_shipping');
                if (s) s.up().up().show();

                $('rule_actions_fieldset').up().show();
                $('rule_promo_sku').up().up().hide();
                $('rule_promo_cats').up().up().hide();
                $('rule_each_m').up().up().hide();
                $('rule_buy_x_get_n').up().up().hide();
                //$('rule_ampromo_type').up().up().hide();

                var s = $('rule_ampromo_type');
                if (s) s.up().up().hide();

                $$('label[for=\"rule_discount_step\"]').first().update('".$this->__('Discount Qty Step (Buy X)')."')

                $('rule_simple_free_shipping').up().up().show();

                $('rule_price_selector').up().up().show();
                $('rule_max_discount').up().up().show();

                if ($('rule_simple_action').value=='by_percent' || $('rule_simple_action').value=='by_fixed'
                || $('rule_simple_action').value=='cart_fixed' || $('rule_simple_action').value=='buy_x_get_y')
                {
                    $('rule_price_selector').up().up().hide();
                    $('rule_max_discount').up().up().hide();
                }

                if ('ampromo_cart' == $('rule_simple_action').value) {
                    $('rule_simple_free_shipping').up().up().hide();

                    $('rule_actions_fieldset').up().hide();
                    $('rule_discount_qty').up().up().hide();
                    $('rule_discount_step').up().up().hide();

                    if (s) s.up().up().hide();
                    $('rule_promo_sku').up().up().show();
                    $('rule_ampromo_type').up().up().show();
                }
                if ('ampromo_items' == $('rule_simple_action').value){
                    $('rule_simple_free_shipping').up().up().hide();

                    if (s) s.up().up().hide();
                    $('rule_promo_sku').up().up().show();
                    $('rule_ampromo_type').up().up().show();
                }
                if ('ampromo_product' == $('rule_simple_action').value){
                    $('rule_simple_free_shipping').up().up().hide();

                    if (s) s.up().up().hide();
                }
                if ('ampromo_spent' == $('rule_simple_action').value){
                    $('rule_simple_free_shipping').up().up().hide();

                    if (s) s.up().up().hide();

                    $('rule_promo_sku').up().up().show();
                    $('rule_ampromo_type').up().up().show();
                }

                if ('$setof_percent' == $('rule_simple_action').value || '$setof_fixed' == $('rule_simple_action').value){
                    $('rule_apply_to_shipping').up().up().hide();
                    $('rule_discount_step').up().up().hide();
                    //$('.rule-tree').hide();

                    $$('div.rule-tree').each(Element.hide);

                }

                if ('$percent' == $('rule_simple_action').value || '$fixed' == $('rule_simple_action').value || '$fixdisc' == $('rule_simple_action').value){
                    $('rule_apply_to_shipping').up().up().hide();
                }

                if ('$each_m_perc' == $('rule_simple_action').value || '$each_m_disc' == $('rule_simple_action').value || '$each_m_fix' == $('rule_simple_action').value){
                    $('rule_each_m').up().up().show();
                    $$('label[for=\"rule_discount_step\"]').first().update('".$this->__('Qty X')."')
                }

				if ('$percent' == $('rule_simple_action').value || '$fixed' == $('rule_simple_action').value || '$fixdisc' == $('rule_simple_action').value || 
                '$setof_percent' == $('rule_simple_action').value || '$setof_fixed' == $('rule_simple_action').value){

					$('rule_promo_sku').up().up().show();
					$('rule_promo_cats').up().up().show();
				}

                if ('$buyxbetnfixed' == $('rule_simple_action').value || '$buyxbetnpercent' == $('rule_simple_action').value || '$buyxbetnfixdisc' == $('rule_simple_action').value ){

					$('rule_promo_sku').up().up().show();
					$('rule_promo_cats').up().up().show();
					$('rule_buy_x_get_n').up().up().show();
				}

                ampromo_note();
			}
			ampromo_hide();
        ";
    }
}