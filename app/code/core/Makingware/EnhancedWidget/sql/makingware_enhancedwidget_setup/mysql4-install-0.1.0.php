<?php

    $installer = $this;
    $installer->startSetup();
    $installer->run(
		"ALTER TABLE {$this->getTable('widget_instance')} ADD `before` VARCHAR(150) DEFAULT '' ;");
    
    $installer->endSetup();
