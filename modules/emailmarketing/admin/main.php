<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Tue, 08 Nov 2016 01:39:51 GMT
 */
if (!defined('NV_IS_FILE_ADMIN')) die('Stop!!!');

if ($nv_Request->isset_request('delete_id', 'get') and $nv_Request->isset_request('delete_checkss', 'get')) {
    $id = $nv_Request->get_int('delete_id', 'get');
    $delete_checkss = $nv_Request->get_string('delete_checkss', 'get');
    if ($id > 0 and $delete_checkss == md5($id . NV_CACHE_PREFIX . $client_info['session_id'])) {
        nv_row_delete($id);
        $nv_Cache->delMod($module_name);
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
        die();
    }
} elseif ($nv_Request->isset_request('delete_list', 'post')) {
    $listall = $nv_Request->get_title('listall', 'post', '');
    $array_id = explode(',', $listall);
    
    if (!empty($array_id)) {
        foreach ($array_id as $id) {
            nv_row_delete($id);
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
$where = '';

$array_search = array(
    'q' => $nv_Request->get_title('q', 'get'),
    'status' => $nv_Request->get_int('status', 'get', -1)
);

if (!empty($array_search['q'])) {
    $base_url .= '&q=' . $array_search['q'];
    $where .= ' AND (title LIKE "%' . $array_search['q'] . '%" OR content LIKE "%' . $array_search['q'] . '%")';
}

if ($array_search['status'] >= 0) {
    $base_url .= '&status=' . $array_search['status'];
    $where .= ' AND sendstatus=' . $array_search['status'];
}

$db->sqlreset()
    ->select('COUNT(*)')
    ->from('' . NV_PREFIXLANG . '_' . $module_data . '_rows')
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
    $view['addtime'] = nv_date('H:i d/m/Y', $view['addtime']);
    $view['begintime'] = $view['typetime'] == 0 ? $lang_module['typetime_0'] : nv_date('H:i d/m/Y', $view['begintime']);
    $view['sendstatusf'] = $lang_module['sendstatus_' . $view['sendstatus']];
    $view['totalemailsend'] = $view['sendstatus'] == 1 ? $view['totalemailsend'] : '-';
    $view['totalmailsuccess'] = $view['sendstatus'] == 1 ? $view['totalmailsuccess'] : '-';
    $view['link_edit'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=content&amp;id=' . $view['id'];
    $view['link_delete'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;delete_id=' . $view['id'] . '&amp;delete_checkss=' . md5($view['id'] . NV_CACHE_PREFIX . $client_info['session_id']);
    $view['link_statics'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=send&amp;id=' . $view['id'];
    $view['link_copy'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=content&amp;id=' . $view['id'] . '&amp;copy=1';
    
    $xtpl->assign('VIEW', $view);
    
    if (in_array($view['sendstatus'], array(
        0,
        2
    ))) {
        $xtpl->parse('main.loop.update');
    } else {
        $xtpl->parse('main.loop.statics');
    }
    
    $xtpl->parse('main.loop');
}

$array_status = array(
    $lang_module['sendstatus_0'],
    $lang_module['sendstatus_1'],
    $lang_module['sendstatus_2']
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
foreach ($array_action as $key => $value) {
    $xtpl->assign('ACTION', array(
        'key' => $key,
        'value' => $value
    ));
    $xtpl->parse('main.action');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['campaign_list'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';