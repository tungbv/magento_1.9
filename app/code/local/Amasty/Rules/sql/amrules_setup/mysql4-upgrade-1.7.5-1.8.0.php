<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();

$this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `price_selector` tinyint unsigned NOT NULL default '0'");

$this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `max_discount` FLOAT unsigned NOT NULL default '0'");

$this->endSetup();
