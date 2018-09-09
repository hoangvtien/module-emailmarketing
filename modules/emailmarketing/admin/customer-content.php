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
} else {
    $row['id'] = 0;
    $row['fullname'] = '';
    $row['gender'] = 1;
    $row['birthday'] = 0;
    $row['phone'] = '';
    $row['customer_group'] = array();

    $lang_module['customer_content'] = $lang_module['customer_add'];

    $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_tmp');
    while ($_row = $result->fetch()) {
        $_row['birthday'] = !empty($_row['birthday']) ? nv_date('d/m/Y', $_row['birthday']) : '';
        $_row['gender'] = $lang_module['gender_' . $_row['gender']];
        $tmp[] = $_row;
    }
}

if ($nv_Request->isset_request('submit', 'post')) {
    $row['fullname'] = $nv_Request->get_title('fullname', 'post', '');
    $row['gender'] = $nv_Request->get_int('gender', 'post', 1);
    $row['email'] = $nv_Request->get_title('email', 'post', '');
    $row['phone'] = $nv_Request->get_title('phone', 'post', '');
    $row['customer_group'] = $nv_Request->get_typed_array('customer_group', 'post', 'int');

    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $nv_Request->get_title('birthday', 'post'), $m)) {
        $row['birthday'] = mktime(23, 59, 59, $m[2], $m[1], $m[3]);
    } else {
        $row['birthday'] = 0;
    }

    if (empty($row['fullname']) and $array_config['requiredfullname']) {
        $error[] = $lang_module['error_required_fullname'];
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

    if (empty($error)) {
        try {
            $new_id = 0;
            if (empty($row['id'])) {
                $_sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_customer(fullname, gender, birthday, phone, email, groups, addtime) VALUES(:fullname, :gender, :birthday, :phone, :email, :groups, ' . NV_CURRENTTIME . ')';
                $data_insert = array();
                $data_insert['fullname'] = $row['fullname'];
                $data_insert['gender'] = $row['gender'];
                $data_insert['birthday'] = $row['birthday'];
                $data_insert['phone'] = $row['phone'];
                $data_insert['email'] = $row['email'];
                $data_insert['groups'] = implode(',', $row['customer_group']);
                $new_id = $db->insert_id($_sql, 'id', $data_insert);
            } else {
                $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET fullname = :fullname, gender = :gender, birthday = :birthday, phone = :phone, email = :email, groups = :groups WHERE id=' . $row['id']);
                $stmt->bindParam(':fullname', $row['fullname'], PDO::PARAM_STR);
                $stmt->bindParam(':gender', $row['gender'], PDO::PARAM_INT);
                $stmt->bindParam(':birthday', $row['birthday'], PDO::PARAM_INT);
                $stmt->bindParam(':phone', $row['phone'], PDO::PARAM_INT);
                $stmt->bindParam(':email', $row['email'], PDO::PARAM_STR);
                $stmt->bindParam(':groups', implode(',', $row['customer_group']), PDO::PARAM_STR);
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

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('OP', $op);
$xtpl->assign('ROW', $row);

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