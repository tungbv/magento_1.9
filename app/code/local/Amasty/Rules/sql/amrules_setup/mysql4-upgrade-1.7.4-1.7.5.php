<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();

$this->run('DELETE FROM '.$this->getTable('salesrule/rule').' WHERE `name`="AmastyXY"');

$this->endSetup();