<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Tue, 08 Nov 2016 01:39:51 GMT
 */
if (!defined('NV_IS_FILE_ADMIN')) die('Stop!!!');

$page_title = $lang_module['config'];

$array_module = array();
if ($nv_Request->isset_request('savesetting', 'post')) {
    $data['allow_declined'] = $nv_Request->get_int('allow_declined', 'post', 0);
    $data['stoperror'] = $nv_Request->get_int('stoperror', 'post', 0);
    $data['numsend'] = $nv_Request->get_int('numsend', 'post', 30);
    $data['customer_data'] = $nv_Request->get_int('customer_data', 'post', 1);
    $data['apikey'] = $nv_Request->get_title('apikey', 'post', '');
    $data['secretkey'] = $nv_Request->get_title('secretkey', 'post', '');

    if (!defined('NV_CUSTOMER')) {
        $data['requiredfullname'] = $nv_Request->get_int('requiredfullname', 'post', 0);
        $data['show_undefine'] = $nv_Request->get_int('show_undefine', 'post', 0);
    }

    $data['new_customer_group'] = $nv_Request->get_typed_array('new_customer_group', 'post', 'int');
    if (!empty($data['new_customer_group'])) {
        $data['new_customer_group'] = implode(',', $data['new_customer_group']);
    } else {
        $data['new_customer_group'] = 0;
    }

    $data_new = array();
    $data['config'] = $nv_Request->get_array('config', 'post');
    foreach ($data['config'] as $index => $value) {
        foreach ($value as $_index => $_value) {
            $_value['active'] = isset($_value['active']) ? $_value['active'] : 0;
            $data_new[$index . '_' . $_index . '_reply'] = $_value['reply'];
            $data_new[$index . '_' . $_index . '_active'] = $_value['active'];
        }
    }

    if (!empty($data_new)) {
        foreach ($data_new as $index => $value) {
            if (isset($module_config[$module_name][$index])) {
                $sql = "UPDATE " . NV_CONFIG_GLOBALTABLE . " SET config_value = :config_value WHERE lang = :lang AND module = :module AND config_name = :config_name";
            } else {
                $sql = 'INSERT INTO ' . NV_CONFIG_GLOBALTABLE . '(lang, module, config_name, config_value ) VALUES(:lang, :module, :config_name, :config_value)';
            }
            $sth = $db->prepare($sql);
            $sth->bindValue(':lang', NV_LANG_DATA, PDO::PARAM_STR);
            $sth->bindParam(':module', $module_name, PDO::PARAM_STR);
            $sth->bindParam(':config_name', $index, PDO::PARAM_STR);
            $sth->bindParam(':config_value', $value, PDO::PARAM_STR);
            $sth->execute();
        }
    }

    $sth = $db->prepare("UPDATE " . NV_CONFIG_GLOBALTABLE . " SET config_value = :config_value WHERE lang = '" . NV_LANG_DATA . "' AND module = :module_name AND config_name = :config_name");
    $sth->bindParam(':module_name', $module_name, PDO::PARAM_STR);
    foreach ($data as $config_name => $config_value) {
        $sth->bindParam(':config_name', $config_name, PDO::PARAM_STR);
        $sth->bindParam(':config_value', $config_value, PDO::PARAM_STR);
        $sth->execute();
    }

    nv_insert_logs(NV_LANG_DATA, $module_name, $lang_module['config'], "Config", $admin_info['userid']);
    $nv_Cache->delMod('settings');

    Header("Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . '=' . $op);
    die();
}

$array_config['ck_requiredfullname'] = $array_config['requiredfullname'] ? 'checked="checked"' : '';
$array_config['ck_allow_declined'] = $array_config['allow_declined'] ? 'checked="checked"' : '';
$array_config['ck_show_undefine'] = $array_config['show_undefine'] ? 'checked="checked"' : '';
$array_config['ck_stoperror'] = $array_config['stoperror'] ? 'checked="checked"' : '';
$array_config['ds_customer_data_1'] = $array_config['customer_data'] == 1 ? '' : 'style="display: none"';

$xtpl = new XTemplate($op . ".tpl", NV_ROOTDIR . "/themes/" . $global_config['module_theme'] . "/modules/" . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('DATA', $array_config);

if (!empty($array_customer_groups)) {
    $array_config['new_customer_group'] = explode(',', $array_config['new_customer_group']);
    foreach ($array_customer_groups as $group) {
        $group['checked'] = in_array($group['id'], $array_config['new_customer_group']) ? 'checked="checked"' : '';
        $xtpl->assign('GROUP', $group);
        $xtpl->parse('main.group');
    }
}

if (isset($site_mods['customer'])) {
    $array_customer_data = array(
        1 => $lang_module['config_customer_data_1'],
        2 => $lang_module['config_customer_data_2']
    );
    foreach ($array_customer_data as $index => $value) {
        $sl = $index == $array_config['customer_data'] ? 'checked="checked"' : '';
        $xtpl->assign('CUSTOMER_DATA', array(
            'index' => $index,
            'value' => $value,
            'checked' => $sl
        ));
        $xtpl->parse('main.customer.customer_data');
    }
    $xtpl->parse('main.customer');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';