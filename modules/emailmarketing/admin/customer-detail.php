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

// change status
if ($nv_Request->isset_request('change_status', 'post, get')) {
    $id = $nv_Request->get_int('id', 'post, get', 0);
    $content = 'NO_' . $id;
    
    $query = 'SELECT status FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE id=' . $id;
    $row = $db->query($query)->fetch();
    if (isset($row['status'])) {
        $status = ($row['status']) ? 0 : 1;
        $query = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET status=' . intval($status) . ' WHERE id=' . $id;
        $db->query($query);
        $content = 'OK_' . $id;
    }
    $nv_Cache->delMod($module_name);
    include NV_ROOTDIR . '/includes/header.php';
    echo $content;
    include NV_ROOTDIR . '/includes/footer.php';
    exit();
}

if ($nv_Request->isset_request('delete_id', 'get') and $nv_Request->isset_request('delete_checkss', 'get')) {
    $id = $nv_Request->get_int('delete_id', 'get');
    $delete_checkss = $nv_Request->get_string('delete_checkss', 'get');
    $redirect = $nv_Request->get_string('redirect', 'get', '');
    if ($id > 0 and $delete_checkss == md5($id . NV_CACHE_PREFIX . $client_info['session_id'])) {
        nv_emailmarketing_customer_delete($id);
        $nv_Cache->delMod($module_name);
        if (!empty($redirect)) {
            Header('Location: ' . nv_redirect_decrypt($redirect));
            die();
        }
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=customer');
        die();
    }
} elseif ($nv_Request->isset_request('delete_list', 'post')) {
    $listall = $nv_Request->get_title('listall', 'post', '');
    $array_id = explode(',', $listall);
    
    if (!empty($array_id)) {
        foreach ($array_id as $id) {
            nv_emailmarketing_customer_delete($id);
        }
        $nv_Cache->delMod($module_name);
        die('OK');
    }
    die('NO');
}

$row = array();
$error = array();
$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$id = $nv_Request->get_int('id', 'get', 0);
$page = $nv_Request->get_int('page', 'post,get', 1);

$db->sqlreset()
    ->select('COUNT(*)')
    ->from(NV_PREFIXLANG . '_' . $module_data . '_customer t1')
    ->where('1=1 AND id=' . $id);

$sth = $db->prepare($db->sql());
$sth->execute();
$num_items = $sth->fetchColumn();

$db->select('*')
    ->order('id DESC')
    ->offset(($page - 1));
$sth = $db->prepare($db->sql());
$sth->execute();

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('OP', $op);

while ($view = $sth->fetch()) {
    $xtpl->assign('CHECK', $view['status'] == 1 ? 'checked' : '');
    $view['birthday'] = nv_date('d/m/Y', $view['birthday']);
    $view['addtime'] = nv_date('H:i d/m/Y', $view['addtime']);
    $view['gender'] = $array_gender[$view['gender']];
    $fullname = $view['fullname'] = nv_show_name_user($view['first_name'], $view['last_name']);
    $view['customer_groups'] = '';
    if ($view['groups'] != '') {
        $customer_groups = array();
        $view['groups'] = explode(',', $view['groups']);
        foreach ($view['groups'] as $groupid) {
            $customer_groups[] = $array_customer_groups[$groupid]['title'];
        }
        $view['customer_groups'] = implode(', ', $customer_groups);
    }
    $view['link_add'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=customer-content';
    $view['link_edit'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=customer-content&amp;id=' . $view['id'];
    $view['link_delete'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;delete_id=' . $view['id'] . '&amp;delete_checkss=' . md5($view['id'] . NV_CACHE_PREFIX . $client_info['session_id']);
    $xtpl->assign('VIEW', $view);
}
$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['customer-detail'].' '.$fullname ;

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';