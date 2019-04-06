<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */
if (!defined('NV_ADMIN')) die('Stop!!!');

$submenu['content'] = $lang_module['campaign_add'];

if (!defined('NV_CUSTOMER')) {
    $submenu['customer'] = $lang_module['customer'];
    $submenu['customer-content'] = $lang_module['customer_add'];
    $submenu['customer-groups'] = $lang_module['customer_groups'];
}

if (defined('NV_IS_SPADMIN')) {
    $submenu['mailserver'] = $lang_module['mailserver'];
    $submenu['sender'] = $lang_module['sender'];
    $submenu['dielist'] = $lang_module['dielist'];
    $submenu['declined'] = $lang_module['declined'];
    $submenu['config'] = $lang_module['config'];
}