#
# @version     $Id: install.joomoocomments.sql,v 1.5 2008/11/03 21:40:41 tomh Exp tomh $
# @author      Tom Hartung <webmaster@tomhartung.com>
# @database    MySql
# @copyright   Copyright (C) 2008 Tom Hartung. All rights reserved.
# @license     TBD
# 
#
#  SQL to create jos_joomoocomments table
#  --------------------------------------
#  using some column names from jos_content
#     created_by: foreign key to jos_users table
#     created: timestamp when comment was added
#
DROP TABLE IF EXISTS `jos_joomoocomments`;
CREATE TABLE IF NOT EXISTS `jos_joomoocomments`
(
	`id` int(11) UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT,
	`created_by` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(50) NOT NULL DEFAULT 'Anonymous Coward',
	`email` VARCHAR(150) NULL DEFAULT '',
	`website` VARCHAR(150) NULL DEFAULT '',
	`ip_address` VARCHAR(40) NULL DEFAULT NULL,
	`text` TEXT NOT NULL DEFAULT '',
	`contentid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`gallerygroupid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`galleryimageid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`published` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`likes` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`dislikes` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`spam` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`ordering` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX (`created_by`),
	INDEX (`contentid`),
	INDEX (`gallerygroupid`),
	INDEX (`galleryimageid`)
) CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

