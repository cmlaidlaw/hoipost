--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(256) NOT NULL,
  `password` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('ACTIVE','DISABLED','DELETED') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `object_has_reply`
--

DROP TABLE IF EXISTS `object_has_reply`;

CREATE TABLE `object_has_reply` (
  `objectId` bigint(20) unsigned NOT NULL,
  `replyObjectId` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`replyObjectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `object_is_establishment`
--

DROP TABLE IF EXISTS `object_is_establishment`;

CREATE TABLE `object_is_establishment` (
  `objectId` bigint(20) unsigned NOT NULL,
  `accountId` int(10) unsigned NOT NULL,
  `serviceLevel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `city` varchar(32) NOT NULL,
  `category` varchar(32) NOT NULL,
  `replyCount` int(10) unsigned NOT NULL DEFAULT '0',
  `lat` decimal(10,6) NOT NULL,
  `lng` decimal(10,6) NOT NULL,
  `geohash` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` enum('ACTIVE','DISABLED','DELETED') NOT NULL DEFAULT 'ACTIVE',
  `statusChanged` int(10) unsigned NOT NULL,
  PRIMARY KEY (`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `object_is_event`
--

DROP TABLE IF EXISTS `object_is_event`;

CREATE TABLE `object_is_event` (
  `objectId` bigint(20) unsigned NOT NULL,
  `accountId` int(10) unsigned NOT NULL,
  `establishmentObjectId` bigint(20) unsigned NOT NULL,
  `serviceLevel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `city` varchar(32) NOT NULL,
  `category` varchar(32) NOT NULL,
  `replyCount` int(10) unsigned NOT NULL DEFAULT '0',
  `lat` decimal(10,6) NOT NULL,
  `lng` decimal(10,6) NOT NULL,
  `geohash` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `startDateTime` datetime NOT NULL,
  `endDateTime` datetime NOT NULL,
  `repeatsWeekly` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` enum('ACTIVE','DISABLED','DELETED') NOT NULL DEFAULT 'ACTIVE',
  `statusChanged` int(10) unsigned NOT NULL,
  PRIMARY KEY (`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `object_is_message`
--

DROP TABLE IF EXISTS `object_is_message`;

CREATE TABLE `object_is_message` (
  `objectId` bigint(20) unsigned NOT NULL,
  `isReply` tinyint(1) NOT NULL DEFAULT '0',
  `replyCount` int(11) unsigned NOT NULL DEFAULT '0',
  `lat` decimal(10,6) NOT NULL,
  `lng` decimal(10,6) NOT NULL,
  `geohash` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `lastActivity` int(10) unsigned NOT NULL,
  `status` enum('ACTIVE','DISABLED','DELETED') NOT NULL DEFAULT 'ACTIVE',
  `statusChanged` int(10) unsigned NOT NULL,
  PRIMARY KEY (`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `objects`
--

DROP TABLE IF EXISTS `objects`;

CREATE TABLE `objects` (
  `objectId` bigint(20) unsigned NOT NULL,
  `body` text COLLATE utf8_bin NOT NULL,
  `lastActivity` int(10) NOT NULL,
  `ipAddress` int(10) unsigned NOT NULL,
  PRIMARY KEY (`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) unsigned NOT NULL,
  `token` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  `ended` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `key` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `vouchers`
--

DROP TABLE IF EXISTS `vouchers`;

CREATE TABLE `vouchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(7) NOT NULL,
  `serviceLevel` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `serviceDuration` int(3) unsigned NOT NULL DEFAULT '30',
  `objectId` bigint(20) unsigned DEFAULT NULL,
  `redeemed` datetime DEFAULT NULL,
  `daysRemaining` int(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;