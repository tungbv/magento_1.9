<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();

$this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `buy_x_get_n` mediumint  unsigned NOT NULL default '0'");

$this->endSetup();