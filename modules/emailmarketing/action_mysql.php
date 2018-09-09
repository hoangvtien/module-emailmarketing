<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Tue, 08 Nov 2016 01:39:51 GMT
 */
if (!defined('NV_IS_FILE_MODULES')) die('Stop!!!');

$sql_drop_module = array();
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_customer";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_customer_groups";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_groups";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_declined";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_die";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_diemobile";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_rows";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_rows_link";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_sender";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_mailserver";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_tmp";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_queue";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_autoresponder";

$sql_create_module = $sql_drop_module;
$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_customer(
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  fullname varchar(255) NOT NULL,
  gender tinyint(1) unsigned NOT NULL DEFAULT '1',
  birthday int(11) unsigned NOT NULL DEFAULT '0',
  phone varchar(20) NOT NULL,
  email varchar(100) NOT NULL,
  groups varchar(255) NOT NULL,
  addtime int(11) unsigned NOT NULL,
  is_die tinyint(1) unsigned NOT NULL DEFAULT '0',
  is_declined tinyint(1) unsigned NOT NULL DEFAULT '0',
  status tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_customer_groups(
  customerid int(11) unsigned NOT NULL,
  groupid smallint(4) unsigned NOT NULL,
  UNIQUE KEY customerid (customerid,groupid)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_groups(
  id smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  note text NOT NULL,
  weight smallint(4) unsigned NOT NULL DEFAULT '0',
  status tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái',
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_declined(
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  email varchar(100) NOT NULL,
  addtime int(11) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_die(
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  email varchar(100) NOT NULL,
  note varchar(255) NOT NULL,
  addtime int(11) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_diemobile(
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  mobile varchar(100) NOT NULL,
  note varchar(255) NOT NULL,
  addtime int(11) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY mobile (mobile)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_rows(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  idsender tinyint(2) unsigned NOT NULL,
  idreplyto tinyint(2) unsigned NOT NULL,
  title varchar(255) NOT NULL,
  content text NOT NULL,
  files text NOT NULL,
  usergroup text NOT NULL,
  customergroup text NOT NULL,
  emaillist text NOT NULL,
  sendlist text NOT NULL COMMENT 'Danh sách khách hàng sẽ gửi',
  sendedlist text NOT NULL COMMENT 'Danh sách khách hàng đã gửi thư',
  errorlist text NOT NULL COMMENT 'Danh sách lỗi',
  openedlist text NOT NULL COMMENT 'Danh sách khách hàng đã mở thư',
  totalemailsend mediumint(8) unsigned NOT NULL DEFAULT '0',
  totalmailsuccess mediumint(8) unsigned NOT NULL DEFAULT '0',
  addtime int(11) unsigned NOT NULL,
  typetime tinyint(1) unsigned NOT NULL DEFAULT '0',
  begintime int(11) unsigned NOT NULL DEFAULT '0',
  endtime int(11) unsigned NOT NULL DEFAULT '0',
  linkstatics tinyint(1) unsigned NOT NULL DEFAULT '1',
  openstatics tinyint(1) unsigned NOT NULL DEFAULT '1',
  sendstatus tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_autoresponder(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  idsender tinyint(2) unsigned NOT NULL,
  idreplyto tinyint(2) unsigned NOT NULL,
  autoresponder_type tinyint(1) unsigned NOT NULL,
  title varchar(255) NOT NULL,
  content text NOT NULL,
  addtime int(11) unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_rows_link(
  rowsid int(11) unsigned NOT NULL,
  text text NOT NULL,
  linkmd5 varchar(32) NOT NULL,
  link text NOT NULL,
  countclick mediumint(8) unsigned NOT NULL DEFAULT '0',
  listclick text NOT NULL
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_sender(
  id tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  weight tinyint(2) NOT NULL DEFAULT '0',
  status tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_mailserver(
  id tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  smtp_host varchar(255) NOT NULL,
  smtp_port smallint(6) unsigned NOT NULL,
  smtp_encrypted tinyint(1) unsigned NOT NULL,
  smtp_username varchar(255) NOT NULL,
  smtp_password varchar(255) NOT NULL,
  sendlimit smallint(4) unsigned NOT NULL,
  weight tinyint(2) NOT NULL DEFAULT '0',
  status tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_tmp(
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  fullname varchar(255) NOT NULL,
  gender tinyint(1) unsigned NOT NULL DEFAULT '1',
  birthday int(11) unsigned NOT NULL DEFAULT '0',
  phone varchar(20) NOT NULL,
  email varchar(100) NOT NULL,
  error text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_queue(
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  from_email varchar(100) NOT NULL,
  from_name varchar(255) NOT NULL,
  to_email varchar(100) NOT NULL,
  subject varchar(255) NOT NULL,
  message text NOT NULL,
  files varchar(255) NOT NULL,
  embeddedimage tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$data = array();
$data['server'] = '1';
$data['requiredfullname'] = 1;
$data['new_customer_group'] = 0;
$data['allow_declined'] = 1;
$data['numsend'] = 30;
$data['show_undefine'] = 0;
$data['stoperror'] = 1;
$data['customer_data'] = 1;

foreach ($data as $config_name => $config_value) {
    $sql_create_module[] = "INSERT INTO " . NV_CONFIG_GLOBALTABLE . " (lang, module, config_name, config_value) VALUES ('" . $lang . "', " . $db->quote($module_name) . ", " . $db->quote($config_name) . ", " . $db->quote($config_value) . ")";
}