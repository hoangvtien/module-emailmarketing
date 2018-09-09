<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Tue, 08 Nov 2016 01:39:51 GMT
 */
if (!defined('NV_IS_FILE_ADMIN')) die('Stop!!!');

// change status
if ($nv_Request->isset_request('change_status', 'post, get')) {
    $id = $nv_Request->get_int('id', 'post, get', 0);
    $content = 'NO_' . $id;

    $query = 'SELECT status FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver WHERE id=' . $id;
    $row = $db->query($query)->fetch();
    if (isset($row['status'])) {
        $status = ($row['status']) ? 0 : 1;
        $query = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver SET status=' . intval($status) . ' WHERE id=' . $id;
        $db->query($query);
        $content = 'OK_' . $id;
    }
    $nv_Cache->delMod($module_name);
    include NV_ROOTDIR . '/includes/header.php';
    echo $content;
    include NV_ROOTDIR . '/includes/footer.php';
    exit();
}

if ($nv_Request->isset_request('ajax_action', 'post')) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $new_vid = $nv_Request->get_int('new_vid', 'post', 0);
    $content = 'NO_' . $id;
    if ($new_vid > 0) {
        $sql = 'SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver WHERE id!=' . $id . ' ORDER BY weight ASC';
        $result = $db->query($sql);
        $weight = 0;
        while ($row = $result->fetch()) {
            ++$weight;
            if ($weight == $new_vid) ++$weight;
            $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver SET weight=' . $weight . ' WHERE id=' . $row['id'];
            $db->query($sql);
        }
        $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver SET weight=' . $new_vid . ' WHERE id=' . $id;
        $db->query($sql);
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
    if ($id > 0 and $delete_checkss == md5($id . NV_CACHE_PREFIX . $client_info['session_id'])) {
        $weight = 0;
        $sql = 'SELECT weight FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver WHERE id =' . $db->quote($id);
        $result = $db->query($sql);
        list ($weight) = $result->fetch(3);

        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver  WHERE id = ' . $db->quote($id));
        if ($weight > 0) {
            $sql = 'SELECT id, weight FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver WHERE weight >' . $weight;
            $result = $db->query($sql);
            while (list ($id, $weight) = $result->fetch(3)) {
                $weight--;
                $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver SET weight=' . $weight . ' WHERE id=' . intval($id));
            }
        }
        $nv_Cache->delMod($module_name);
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
        die();
    }
}

$row = array();
$error = array();
$row['id'] = $nv_Request->get_int('id', 'post,get', 0);

$smtp_encrypted_array = array();
$smtp_encrypted_array[0] = 'None';
$smtp_encrypted_array[1] = 'SSL';
$smtp_encrypted_array[2] = 'TLS';

if ($nv_Request->isset_request('submit', 'post')) {
    $row['smtp_host'] = $nv_Request->get_title('smtp_host', 'post', '');
    $row['smtp_port'] = $nv_Request->get_int('smtp_port', 'post', '');
    $row['smtp_encrypted'] = $nv_Request->get_int('smtp_encrypted', 'post', 0);
    $row['smtp_username'] = $nv_Request->get_title('smtp_username', 'post', '');
    $row['smtp_password'] = $nv_Request->get_title('smtp_password', 'post', '');
    $row['smtp_password'] = $crypt->encrypt($row['smtp_password']);
    $row['sendlimit'] = $nv_Request->get_int('sendlimit', 'post', 0);

    if (empty($row['smtp_host'])) {
        $error[] = $lang_module['error_required_smtp_host'];
    } elseif (empty($row['smtp_port'])) {
        $error[] = $lang_module['error_required_smtp_port'];
    } elseif ($row['smtp_encrypted'] == 1) {
        require_once NV_ROOTDIR . '/includes/core/phpinfo.php';
        $array_phpmod = phpinfo_array(8, 1);
        if (!empty($array_phpmod) and !array_key_exists('openssl', $array_phpmod)) {
            $error[] = $lang_module['error_required_openssl'];
        }
    }

    if (empty($row['id'])) {
        $count = $db->query('SELECT COUNT(*) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver WHERE smtp_host = ' . $db->quote($row['smtp_host']) . ' AND smtp_port = ' . $row['smtp_port'] . ' AND smtp_encrypted = ' . $row['smtp_encrypted'] . ' AND smtp_username = ' . $db->quote($row['smtp_username']) . ' AND smtp_password = ' . $db->quote($row['smtp_password']))
            ->fetchColumn();
        if ($count) {
            $error[] = $lang_module['error_data_exists'];
        }
    }

    if (empty($error)) {
        try {
            if (empty($row['id'])) {
                $stmt = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver (smtp_host, smtp_port, smtp_encrypted, smtp_username, smtp_password, sendlimit, weight) VALUES (:smtp_host, :smtp_port, :smtp_encrypted, :smtp_username, :smtp_password, :sendlimit, :weight)');

                $weight = $db->query('SELECT max(weight) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver')->fetchColumn();
                $weight = intval($weight) + 1;
                $stmt->bindParam(':weight', $weight, PDO::PARAM_INT);
            } else {
                $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver SET smtp_host = :smtp_host, smtp_port = :smtp_port, smtp_encrypted = :smtp_encrypted, smtp_username = :smtp_username, smtp_password = :smtp_password, sendlimit = :sendlimit WHERE id=' . $row['id']);
            }
            $stmt->bindParam(':smtp_host', $row['smtp_host'], PDO::PARAM_STR);
            $stmt->bindParam(':smtp_port', $row['smtp_port'], PDO::PARAM_STR);
            $stmt->bindParam(':smtp_encrypted', $row['smtp_encrypted'], PDO::PARAM_INT);
            $stmt->bindParam(':smtp_username', $row['smtp_username'], PDO::PARAM_STR);
            $stmt->bindParam(':smtp_password', $row['smtp_password'], PDO::PARAM_STR);
            $stmt->bindParam(':sendlimit', $row['sendlimit'], PDO::PARAM_INT);

            $exc = $stmt->execute();
            if ($exc) {
                $nv_Cache->delMod($module_name);
                Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
                die();
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            die($e->getMessage()); // Remove this line after checks finished
        }
    }
} elseif ($row['id'] > 0) {
    $row = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mailserver WHERE id=' . $row['id'])->fetch();
    if (empty($row)) {
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
        die();
    }
} else {
    $row['id'] = 0;
    $row['smtp_host'] = '';
    $row['smtp_port'] = '';
    $row['smtp_encrypted'] = '';
    $row['smtp_username'] = '';
    $row['smtp_password'] = '';
    $row['sendlimit'] = '';
}

$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$per_page = 15;
$page = $nv_Request->get_int('page', 'post,get', 1);

$db->sqlreset()
    ->select('COUNT(*)')
    ->from('' . NV_PREFIXLANG . '_' . $module_data . '_mailserver');

$sth = $db->prepare($db->sql());

$sth->execute();
$num_items = $sth->fetchColumn();

$db->select('*')
    ->order('weight ASC')
    ->limit($per_page)
    ->offset(($page - 1) * $per_page);

$sth = $db->prepare($db->sql());
$sth->execute();

$row['smtp_password'] = !empty($row['smtp_password']) ? $crypt->decrypt($row['smtp_password']) : '';
$row['sendlimit'] = !empty($row['sendlimit']) ? $row['sendlimit'] : '';

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);
$xtpl->assign('ROW', $row);

while ($view = $sth->fetch()) {
    for ($i = 1; $i <= $num_items; ++$i) {
        $xtpl->assign('WEIGHT', array(
            'key' => $i,
            'title' => $i,
            'selected' => ($i == $view['weight']) ? ' selected="selected"' : ''
        ));
        $xtpl->parse('main.loop.weight_loop');
    }
    $xtpl->assign('CHECK', $view['status'] == 1 ? 'checked' : '');
    $view['smtp_encrypted'] = $smtp_encrypted_array[$view['smtp_encrypted']];
    $view['link_edit'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $view['id'];
    $view['link_delete'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;delete_id=' . $view['id'] . '&amp;delete_checkss=' . md5($view['id'] . NV_CACHE_PREFIX . $client_info['session_id']);
    $xtpl->assign('VIEW', $view);
    $xtpl->parse('main.loop');
}

$generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
if (!empty($generate_page)) {
    $xtpl->assign('NV_GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}

foreach ($smtp_encrypted_array as $index => $value) {
    $sl = $index == $row['smtp_encrypted'] ? 'selected="selected"' : '';
    $xtpl->assign('SMTP_EMCRYPTED', array(
        'index' => $index,
        'value' => $value,
        'selected' => $sl
    ));
    $xtpl->parse('main.smtp_encrypted');
}

if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['mailserver'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';