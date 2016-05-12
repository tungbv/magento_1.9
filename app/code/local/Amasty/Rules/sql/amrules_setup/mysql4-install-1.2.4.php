<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();


$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('salesrule/rule');
$cols = $this->getConnection()->fetchCol($fieldsSql);

if (!in_array('promo_sku', $cols)){
    $this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `promo_sku` TEXT");
}
$this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `promo_cats` TEXT");

$this->endSetup();