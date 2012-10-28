
#
# SQL to create one or more rows in the jos_joomoocomments table
#
select * from jos_joomoocomments;
select count(*) from jos_joomoocomments;

select fp.content_id, cont.title FROM jos_content_frontpage AS fp, jos_content AS cont WHERE fp.content_id = cont.id order by fp.content_id DESC;

#
#  Changes we made to the table after we created it.
#
ALTER TABLE `jos_joomoocomments` ADD COLUMN `email` VARCHAR(150) NULL DEFAULT '' AFTER `name`;
ALTER TABLE `jos_joomoocomments` ADD COLUMN `link` VARCHAR(150) NULL DEFAULT '' AFTER `email`;
ALTER TABLE `jos_joomoocomments` CHANGE COLUMN `link` `website` VARCHAR(150) NULL DEFAULT '';

ALTER TABLE `jos_joomoocomments` CHANGE COLUMN `text` `text` TEXT NOT NULL DEFAULT '';
#
# We are not going to try to do nested comments at this time
# Eg. how to deal with parent post being deleted?
#     (perhaps disable the delete function when using nested comments?)
#  `parentid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
#
ALTER TABLE `jos_joomoocomments` DROP COLUMN `parentid`;

ALTER TABLE `jos_joomoocomments` ADD COLUMN `ip_address` VARCHAR(40) NULL DEFAULT NULL AFTER `website`;
ALTER TABLE `jos_joomoocomments` ADD COLUMN `likes` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `published`;
ALTER TABLE `jos_joomoocomments` ADD COLUMN `dislikes` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `likes`;
ALTER TABLE `jos_joomoocomments` ADD COLUMN `spam` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `dislikes`;

ALTER TABLE `jos_joomoocomments` ADD COLUMN `galleryimageid` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `contentid`;
ALTER TABLE `jos_joomoocomments` ADD INDEX (galleryimageid);

ALTER TABLE `jos_joomoocomments` ADD COLUMN `gallerygroupid` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `contentid`;
ALTER TABLE `jos_joomoocomments` ADD INDEX (gallerygroupid);

