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


function nv_users_add($username, $password, $email, $first_name, $last_name, $gender, $birthday = 0, $adduser_email = 1)
{
    global $db, $global_config, $user_info, $nv_Cache, $crypt, $lang_module;
    
    // chế độ import dữ liệu
    $groups_list = nv_groups_list();
    
    $_user = array();
    $_user['view_mail'] = 0;
    $_user['in_groups'] = array(
        4 // thành viên chính thức
    );
    $_user['in_groups_default'] = 0;
    $_user['is_official'] = 1;
    
    // xác định nhóm thành viên
    $in_groups = array();
    foreach ($_user['in_groups'] as $_group_id) {
        if ($_group_id > 9) {
            $in_groups[] = $_group_id;
        }
    }
    $_user['in_groups'] = array_intersect($in_groups, array_keys($groups_list));
    
    if (empty($_user['is_official'])) {
        $_user['in_groups'][] = 7;
        $_user['in_groups_default'] = 7;
    } elseif (empty($_user['in_groups_default']) or !in_array($_user['in_groups_default'], $_user['in_groups'])) {
        $_user['in_groups_default'] = 4;
    }
    
    if (empty($_user['in_groups_default']) and sizeof($_user['in_groups'])) {
        trigger_error($lang_module['edit_error_group_default']);
        return 0;
    }
    
    $sql = "INSERT INTO " . NV_USERS_GLOBALTABLE . " (
                    group_id, username, md5username, password, email, first_name, last_name, gender, birthday, sig, regdate,
                    question, answer, passlostkey, view_mail,
                    remember, in_groups, active, checknum, last_login, last_ip, last_agent, last_openid, idsite)
                VALUES (
                    " . $_user['in_groups_default'] . ",
                    :username,
                    :md5_username,
                    :password,
                    :email,
                    :first_name,
                    :last_name,
                    :gender,
                    " . $birthday . ",
                    :sig,
                    " . NV_CURRENTTIME . ",
                    :question,
                    :answer,
                    '',
                     " . $_user['view_mail'] . ",
                     1,
                     '" . implode(',', $_user['in_groups']) . "', 1, '', 0, '', '', '', " . $global_config['idsite'] . "
                )";
    $data_insert = array();
    $data_insert['username'] = $username;
    $data_insert['md5_username'] = nv_md5safe($username);
    $data_insert['password'] = $crypt->hash_password($password, $global_config['hashprefix']);
    $data_insert['email'] = $email;
    $data_insert['first_name'] = $first_name;
    $data_insert['last_name'] = $last_name;
    $data_insert['gender'] = $gender;
    $data_insert['sig'] = '';
    $data_insert['question'] = '';
    $data_insert['answer'] = '';
    $userid = $db->insert_id($sql, 'userid', $data_insert);
    
    if (!$userid) {
        trigger_error($lang_module['error_unknow']);
        return 0;
    }    
    
    if (!empty($_user['in_groups'])) {
        foreach ($_user['in_groups'] as $group_id) {
            if ($group_id != 7) {
                nv_groups_add_user($group_id, $userid, 1, $module_data);
            }
        }
    }
    $db->query('UPDATE ' . NV_USERS_GLOBALTABLE . '_groups SET numbers = numbers+1 WHERE group_id=' . ($_user['is_official'] ? 4 : 7));
    $nv_Cache->delMod('users');
    
    // Gửi mail thông báo
    if (!empty($adduser_email)) {
        $full_name = nv_show_name_user($first_name, $last_name, $username);
        $subject = $lang_module['adduser_register'];
        $_url = NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=users', true);
        $message = sprintf($lang_module['adduser_register_info1'], $full_name, $global_config['site_name'], $_url, $username, $password);
        @nv_sendmail($global_config['site_email'], $email, $subject, $message);
    }
    
    return $userid;
}

/**
 * nv_groups_list()
 *
 * @return
 */


