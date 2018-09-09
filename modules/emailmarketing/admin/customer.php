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
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
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

if ($nv_Request->isset_request('customerlist', 'post')) {
    $action = $nv_Request->get_title('action', 'post', '');
    $listall = $nv_Request->get_title('listall', 'post', '');
    $array_id = explode(',', $listall);

    if (!empty($array_id) and !empty($action)) {
        foreach ($array_id as $id) {
            if ($action == 'adddie_list_id') {
                $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET is_die=1 WHERE id=' . $id);
            } elseif ($action == 'removedie_list_id') {
                $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET is_die=0 WHERE id=' . $id);
            } elseif ($action == 'removedeclined_list_id') {
                $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET is_declined=0 WHERE id=' . $id);
            }
        }
        $nv_Cache->delMod($module_name);
        die('OK');
    }
    die('NO');
}

$row = array();
$error = array();
$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$per_page = 20;
$page = $nv_Request->get_int('page', 'post,get', 1);
$mod = $nv_Request->get_title('mod', 'post,get', '');
$where = $join = '';

$array_search = array(
    'q' => $nv_Request->get_title('q', 'post,get'),
    'group' => $nv_Request->get_int('group', 'post,get', -1),
    'status' => $nv_Request->get_int('status', 'post,get', -1)
);

if (!empty($array_search['q'])) {
    $base_url .= '&q=' . $array_search['q'];
    $where .= ' AND (fullname LIKE "%' . $array_search['q'] . '%" OR email LIKE "%' . $array_search['q'] . '%")';
}

if ($array_search['group'] >= 0) {
    $base_url .= '&group=' . $array_search['group'];
    $join = 'INNER JOIN ' . NV_PREFIXLANG . '_' . $module_data . '_customer_groups t2 ON t1.id=t2.customerid';
    $where .= ' AND t2.groupid=' . $array_search['group'];
} elseif (!$array_config['show_undefine']) {
    //$where .= ' AND groups != "0"';
}

if ($array_search['status'] >= 0) {
    $base_url .= '&status=' . $array_search['status'];
    $where .= ' AND status=' . $array_search['status'];
}

if ($mod == 'declined') {
    $base_url .= '&mod=declined';
    $where .= ' AND is_declined=1';
    $set_active_op = 'declined';
} elseif ($mod == 'die') {
    $base_url .= '&mod=die';
    $where .= ' AND is_die=1';
    $set_active_op = 'dielist';
} else {
    $where .= ' AND is_die=0 AND is_declined=0';
}

$db->sqlreset()
    ->select('COUNT(*)')
    ->from(NV_PREFIXLANG . '_' . $module_data . '_customer t1')
    ->join($join)
    ->where('1=1' . $where);

$sth = $db->prepare($db->sql());
$sth->execute();
$num_items = $sth->fetchColumn();

$db->select('*')
    ->order('id DESC')
    ->limit($per_page)
    ->offset(($page - 1) * $per_page);
$sth = $db->prepare($db->sql());
$sth->execute();

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('OP', $op);
$xtpl->assign('ROW', $row);
$xtpl->assign('SEARCH', $array_search);
$xtpl->assign('BASE_URL', $base_url);

$generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
if (!empty($generate_page)) {
    $xtpl->assign('NV_GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}
$number = $page > 1 ? ($per_page * ($page - 1)) + 1 : 1;
while ($view = $sth->fetch()) {
    $view['number'] = $number++;
    $xtpl->assign('CHECK', $view['status'] == 1 ? 'checked' : '');
    $view['addtime'] = nv_date('H:i d/m/Y', $view['addtime']);
    $view['gender'] = $array_gender[$view['gender']];
    $view['customer_groups'] = '';
    if ($view['groups'] != '') {
        $customer_groups = array();
        $view['groups'] = explode(',', $view['groups']);
        foreach ($view['groups'] as $groupid) {
            $customer_groups[] = $array_customer_groups[$groupid]['title'];
        }
        $view['customer_groups'] = implode(', ', $customer_groups);
    }
    $view['link_edit'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=customer-content&amp;id=' . $view['id'];
    $view['link_delete'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;delete_id=' . $view['id'] . '&amp;delete_checkss=' . md5($view['id'] . NV_CACHE_PREFIX . $client_info['session_id']);
    $xtpl->assign('VIEW', $view);
    $xtpl->parse('main.loop');
}

if (!empty($array_customer_groups)) {
    foreach ($array_customer_groups as $groups) {
        $groups['selected'] = $array_search['group'] == $groups['id'] ? 'selected="selected"' : '';
        $xtpl->assign('CUSTOMER_GROUP', $groups);
        $xtpl->parse('main.customer_group');
    }
}

$array_status = array(
    0 => $lang_module['inactive'],
    1 => $lang_module['active']
);
foreach ($array_status as $index => $value) {
    $sl = $index == $array_search['status'] ? 'selected="selected"' : '';
    $xtpl->assign('STATUS', array(
        'index' => $index,
        'value' => $value,
        'selected' => $sl
    ));
    $xtpl->parse('main.status');
}

$array_action = array(
    'delete_list_id' => $lang_global['delete']
);

if ($mod == 'declined') {
    $array_action += array(
        'removedeclined_list_id' => $lang_module['removefromdeclined']
    );
} elseif ($mod == 'die') {
    $array_action += array(
        'removedie_list_id' => $lang_module['removefromdie']
    );
} else {
    $array_action += array(
        'adddie_list_id' => $lang_module['addtodie']
    );
}

foreach ($array_action as $key => $value) {
    $xtpl->assign('ACTION', array(
        'key' => $key,
        'value' => $value
    ));
    $xtpl->parse('main.action');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['customer'];

if (!empty($mod)) {
    $op = '';
}

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';