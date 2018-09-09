<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */
if (!defined('NV_ADMIN') or !defined('NV_MAINFILE') or !defined('NV_IS_MODADMIN')) die('Stop!!!');

define('NV_IS_FILE_ADMIN', true);
require_once NV_ROOTDIR . '/modules/' . $module_file . '/global.functions.php';

$allow_func = array(
    'main',
    'config',
    'customer',
    'customer-groups',
    'customer-content',
    'content',
    'dielist',
    'sender',
    'declined',
    'send',
    'mailserver'
);

$array_gender = array(
    1 => $lang_module['gender_1'],
    0 => $lang_module['gender_0'],
    2 => $lang_module['gender_2']
);

$array_customer_groups[0] = array(
    'id' => 0,
    'title' => $lang_module['undefine'],
    'weight' => 0,
    'status' => 1
);

if (defined('NV_CUSTOMER')) {
    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_customer_types WHERE active=1 ORDER BY weight';
    $array_customer_groups += $nv_Cache->db($sql, 'id', 'customer');
} else {
    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_groups WHERE status=1 ORDER BY weight';
    $array_customer_groups += $nv_Cache->db($sql, 'id', $module_name);
}

function nv_emailmarketing_customer_delete($id)
{
    global $db, $module_data;

    $count = $db->exec('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE id =' . $id);
    if ($count) {
        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer_groups WHERE customerid =' . $id);
    }
}

function nv_row_delete($id)
{
    global $db, $module_data;

    $count = $db->exec('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id =' . $id);
    if ($count) {
        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link WHERE rowsid =' . $id);
    }
}

function nv_emailmarketing_check_mobile($phone)
{
    global $lang_module;
    if (!preg_match("/^[0-9]{10,11}$/", $phone)) {
        return $lang_module['mobile_is_error'];
    }
}
