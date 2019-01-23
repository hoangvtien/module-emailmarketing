<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Tue, 08 Nov 2016 01:39:51 GMT
 */
if (!defined('NV_IS_FILE_ADMIN')) die('Stop!!!');

if (defined('NV_CUSTOMER')) {
    Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
    die();
}

if ($nv_Request->isset_request('get_user_json', 'post, get')) {
    $q = $nv_Request->get_title('q', 'post, get', '');
    
    $db->sqlreset()
    ->select('userid, first_name, last_name, username, email')
    ->from(NV_USERS_GLOBALTABLE)
    ->where('(first_name LIKE "%' . $q . '%"
            OR last_name LIKE "%' . $q . '%"
            OR username LIKE "%' . $q . '%"
            OR email LIKE "%' . $q . '%"
        ) AND userid NOT IN (SELECT userid_link FROM ' . NV_PREFIXLANG . '_emailmarketing_customer)')
        ->order('first_name ASC')
        ->limit(20);
        
        $sth = $db->prepare($db->sql());
        $sth->execute();
        
        $array_data = array();
        while (list ($userid, $first_name, $last_name, $username, $email) = $sth->fetch(3)) {
            $array_data[] = array(
                'id' => $userid,
                'fullname' => nv_show_name_user($first_name, $last_name, $username),
                'email' => $email
            );
        }
        
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        
        ob_start('ob_gzhandler');
        echo json_encode($array_data);
        exit();
}

$row = array();
$error = array();
$tmp = array();

$row['id'] = $nv_Request->get_int('id', 'post,get', 0);

if ($row['id'] > 0) {
    $lang_module['customer_content'] = $lang_module['customer_edit'];
    $row = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE id=' . $row['id'])->fetch();
    if (empty($row)) {
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=customer');
        die();
    }
    
    $row['customer_group'] = $row['customer_group_old'] = array();
    $result = $db->query('SELECT groupid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer_groups WHERE customerid=' . $row['id']);
    while (list ($groupid) = $result->fetch(3)) {
        $row['customer_group'][] = $groupid;
    }
    $row['customer_group_old'] = $row['customer_group'];
    
    $row['userid_link_type'] = 0;
    if ($row['userid_link'] > 0) {
        $row['userid_link_type'] = 1;
    }
     
} else {
    $row['id'] = 0;
    $row['first_name'] = '';
    $row['last_name'] = '';
    $row['gender'] = 1;
    $row['birthday'] = 0;
    $row['phone'] = '';
    $row['customer_group'] = array();
    
    $lang_module['customer_content'] = $lang_module['customer_add'];
    $row['userid_link'] = $row['userid_link_type'] = 0;
    $row['username'] = $row['password'] = $row['password1'] = '';
    
    $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_tmp');
    while ($_row = $result->fetch()) {
        $_row['birthday'] = !empty($_row['birthday']) ? nv_date('d/m/Y', $_row['birthday']) : '';
        $_row['gender'] = $lang_module['gender_' . $_row['gender']];
        $tmp[] = $_row;
    }
}

if ($nv_Request->isset_request('submit', 'post')) {
    $row['first_name'] = $nv_Request->get_title('first_name', 'post', '');
    $row['last_name'] = $nv_Request->get_title('last_name', 'post', '');
    $row['gender'] = $nv_Request->get_int('gender', 'post', 1);
    $row['email'] = $nv_Request->get_title('email', 'post', '');
    $row['phone'] = $nv_Request->get_title('phone', 'post', '');
    $row['customer_group'] = $nv_Request->get_typed_array('customer_group', 'post', 'int');
    
    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $nv_Request->get_title('birthday', 'post'), $m)) {
        $row['birthday'] = mktime(23, 59, 59, $m[2], $m[1], $m[3]);
    } else {
        $row['birthday'] = 0;
    }
    
    if (empty($row['first_name']) and $array_config['requiredfullname']) {
        $error[] = $lang_module['error_required_first_name'];
    } elseif (empty($row['customer_group'])) {
        $error[] = $lang_module['error_required_customer_group'];
    } elseif (!empty($row['email']) and ($error_email = nv_check_valid_email($row['email'])) != '') {
        $error[] = $error_email;
    } elseif (empty($row['id'])) {
        $count = $db->query('SELECT COUNT(*) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE email=' . $db->quote($row['email']))
            ->fetchColumn();
        if ($count > 0) {
            $error[] = sprintf($lang_module['error_email_exists'], $row['email']);
        }
    }
    
    $row['userid_link_type'] = $nv_Request->get_int('userid_link_type', 'post', 0);
    if ($row['userid_link_type'] == 0) {
        $row['userid_link'] = 0;
    } elseif ($row['userid_link_type'] == 1) {
        $row['userid_link'] = $nv_Request->get_int('userid_link', 'post', 1);
        if (empty($row['userid_link'])) {
            $error[] = $lang_module['error_required_userid_link'];
        }
    } elseif ($row['userid_link_type'] == 2) {
        $row['email'] = $nv_Request->get_title('email', 'post', '');
        $row['username'] = $nv_Request->get_title('username', 'post', '');
        $row['password'] = $nv_Request->get_title('password', 'post', '');
        $row['password1'] = $nv_Request->get_title('password1', 'post', '');
        $row['adduser_email'] = $nv_Request->get_int('adduser_email', 'post', 0);
        
        if (($check = nv_check_valid_email($row['email'])) != '') {
            $error[] = $check;
        }
        
        if (empty($row['username'])) {
            $error[] = $lang_module['error_required_username'];
        }
        
        if (!empty($row['password']) && $row['password'] != $row['password1']) {
            $error[] = $lang_module['error_password_like'];
        }
        
        if (empty($row['password'])) {
            $_len = round(($global_config['nv_upassmin'] + $global_config['nv_upassmax']) / 2);
            $row['password'] = nv_genpass($_len, $global_config['nv_upass_type']);
        }
    }
    
    if (empty($error)) {
        try {
            $new_id = 0;
            if (empty($row['id'])) {
                $_sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_customer(first_name, last_name, gender, birthday, phone, email, groups, addtime,userid_link) VALUES(:first_name, :last_name, :gender, :birthday, :phone, :email, :groups, ' . NV_CURRENTTIME . ', :userid_link)';
                $data_insert = array();
                $data_insert['first_name'] = $row['first_name'];
                $data_insert['last_name'] = $row['last_name'];
                $data_insert['gender'] = $row['gender'];
                $data_insert['birthday'] = $row['birthday'];
                $data_insert['phone'] = $row['phone'];
                $data_insert['email'] = $row['email'];
                $data_insert['groups'] = implode(',', $row['customer_group']);
                $data_insert['userid_link'] = $row['userid_link'];
                $new_id = $db->insert_id($_sql, 'id', $data_insert);
            } else {
                $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET first_name = :first_name, last_name = :last_name, gender = :gender, birthday = :birthday, phone = :phone, email = :email, groups = :groups, userid_link = :userid_link WHERE id=' . $row['id']);
                $stmt->bindParam(':first_name', $row['first_name'], PDO::PARAM_STR);
                $stmt->bindParam(':last_name', $row['last_name'], PDO::PARAM_STR);
                $stmt->bindParam(':gender', $row['gender'], PDO::PARAM_INT);
                $stmt->bindParam(':birthday', $row['birthday'], PDO::PARAM_INT);
                $stmt->bindParam(':phone', $row['phone'], PDO::PARAM_INT);
                $stmt->bindParam(':email', $row['email'], PDO::PARAM_STR);
                $stmt->bindParam(':groups', implode(',', $row['customer_group']), PDO::PARAM_STR);
                $stmt->bindParam(':userid_link', $row['userid_link'], PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $new_id = $row['id'];
                }
            }
            
            if ($new_id > 0) {
                
                if ($row['customer_group'] != $row['customer_group_old']) {
                    $sth = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_customer_groups (customerid, groupid) VALUES(:customerid, :groupid)');
                    foreach ($row['customer_group'] as $customer_group_id) {
                        if (!in_array($customer_group_id, $row['customer_group_old'])) {
                            $sth->bindParam(':customerid', $new_id, PDO::PARAM_INT);
                            $sth->bindParam(':groupid', $customer_group_id, PDO::PARAM_INT);
                            $sth->execute();
                        }
                    }
                    
                    foreach ($row['customer_group_old'] as $customer_group_old) {
                        if (!in_array($customer_group_old, $row['customer_group'])) {
                            $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer_groups WHERE groupid = ' . $customer_group_old . ' AND customerid=' . $new_id);
                        }
                    }
                }

                if ($row['userid_link_type'] == 2) {
                 
                    $row['gender'] = $row['gender'] ? 'M' : 'F';
                    $userid = nv_users_add($row['username'], $row['password'], $row['email'], $row['first_name'], $row['last_name'], $row['gender'], $row['birthday'], $row['adduser_email']);
                    if ($userid > 0) {
                        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET userid_link=' . $userid . ' WHERE id=' . $new_id);
                    }
                }
                $nv_Cache->delMod($module_name);
                Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=customer');
                die();
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            die($e->getMessage()); // Remove this line after checks finished
        }
    }
}

$row['birthday'] = !empty($row['birthday']) ? nv_date('d/m/Y', $row['birthday']) : '';

$user = array();
if ($row['userid_link'] > 0) {
    $user = $db->query('SELECT userid, first_name, last_name, username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $row['userid_link'])->fetch();
}

$row['userid_link_type_1_style'] = $row['userid_link_type_2_style'] = 'style="display: none"';
if ($row['userid_link_type'] == 1) {
    $row['userid_link_type_1_style'] = '';
} elseif ($row['userid_link_type'] == 2) {
    $row['userid_link_type_2_style'] = '';
}


$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('OP', $op);
$xtpl->assign('ROW', $row);

$array_userid_link_type = array(
    0 => $lang_module['userid_link_0'],
    1 => $lang_module['userid_link_1'],
    2 => $lang_module['userid_link_2']
);
foreach ($array_userid_link_type as $index => $value) {
    $xtpl->assign('OPTION', array(
        'key' => $index,
        'title' => $value,
        'checked' => $row['userid_link_type'] == $index ? 'checked="checked"' : ''
    ));
    $xtpl->parse('main.userid_link_type');
}

if (!empty($user)) {
    $user['fullname'] = nv_show_name_user($user['first_name'], $user['last_name']);
    $xtpl->assign('USER', $user);
    $xtpl->parse('main.user');
}

if (!empty($tmp)) {
    $number = 1;
    $import_error = 0;
    foreach ($tmp as $_tmp) {
        $_tmp['number'] = $number++;
        $xtpl->assign('TMP', $_tmp);
        
        if (empty($_tmp['error'])) {
            $xtpl->parse('main.tmp.loop.vaild');
        } else {
            $import_error++;
            $xtpl->parse('main.tmp.loop.error');
        }
        
        $xtpl->parse('main.tmp.loop');
    }
    
    if ($import_error > 0) {
        $xtpl->assign('IMPORT_ERROR', sprintf($lang_module['error_note'], $import_error));
        $xtpl->parse('main.tmp.error');
        $xtpl->parse('main.tmp.error_btn');
        $xtpl->parse('main.tmp.error_skip_error');
    }
    
    if (!empty($array_customer_groups)) {
        foreach ($array_customer_groups as $groups) {
            $xtpl->assign('CUSTOMER_GROUP', $groups);
            $xtpl->parse('main.tmp.customer_group');
        }
    }
    
    $xtpl->parse('main.tmp');
}

if (!empty($array_customer_groups)) {
    foreach ($array_customer_groups as $groups) {
        $groups['checked'] = in_array($groups['id'], $row['customer_group']) ? 'checked="checked"' : '';
        $xtpl->assign('CUSTOMER_GROUP', $groups);
        $xtpl->parse('main.customer_group');
    }
}

foreach ($array_gender as $index => $value) {
    $ck = $index == $row['gender'] ? 'checked="checked"' : '';
    $xtpl->assign('GENDER', array(
        'index' => $index,
        'value' => $value,
        'checked' => $ck
    ));
    $xtpl->parse('main.gender');
}

if ($array_config['requiredfullname']) {
    $xtpl->parse('main.requiredfullname1');
    $xtpl->parse('main.requiredfullname2');
    $xtpl->parse('main.requiredfullname3');
}

if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['customer_content'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';