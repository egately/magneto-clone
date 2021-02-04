<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'before' => [
        'store' => 'CREATE TABLE `store` (
  `store_owner_id` smallint DEFAULT NULL COMMENT \'Store Owner Reference\',
  KEY `STORE_STORE_OWNER_ID_STORE_OWNER_OWNER_ID` (`store_owner_id`),
  CONSTRAINT `STORE_STORE_OWNER_ID_STORE_OWNER_OWNER_ID` FOREIGN KEY (`store_owner_id`) REFERENCES `store_owner` (`owner_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
        'store_owner' => 'CREATE TABLE `store_owner` (
  `owner_id` smallint NOT NULL AUTO_INCREMENT,
  `store_owner_name` varchar(255) DEFAULT NULL COMMENT \'Store Owner Name\',
  PRIMARY KEY (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Store owner information\''
    ],
    'after' => [
        'store' => 'CREATE TABLE `store` (
  `store_owner` varchar(255) DEFAULT NULL COMMENT \'Store Owner Name\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8'
    ]
];
