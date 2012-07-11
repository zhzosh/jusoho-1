<?php
    $installer = $this;
    $installer->startSetup();
    $installer->run("
        DROP TABLE IF EXISTS {$this->getTable('eav_attribute_image_option_product_pic')};
        CREATE TABLE `eav_attribute_image_option_product_pic` (
          `pic_id` int(10) NOT NULL AUTO_INCREMENT,
          `option_id` int(10) NOT NULL DEFAULT '0',
          `product_id` int(10) NOT NULL DEFAULT '0',
          `color_text` varchar(10) DEFAULT '',
          `color_pic` varchar(255) DEFAULT '',
          PRIMARY KEY (`pic_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    ");
    $installer->endSetup();