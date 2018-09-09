<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Tue, 08 Nov 2016 01:39:51 GMT
 */
if (!defined('NV_IS_FILE_ADMIN')) die('Stop!!!');

$row = array();
$error = array();
$row['id'] = $nv_Request->get_int('id', 'post,get', 0);
$draft = $nv_Request->isset_request('draft', 'post');

if ($row['id'] > 0) {
    $lang_module['campaign_add'] = $lang_module['campaign_edit'];
    $row = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id=' . $row['id'] . ' AND sendstatus != 1')->fetch();
    if (empty($row) or $row['sendstatus'] == 1) {
        Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
        die();
    }
    $row['linkmd5'] = $row['linkmd5_old'] = array();
    $result = $db->query('SELECT linkmd5 FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link WHERE rowsid=' . $row['id']);
    while (list ($linkmd5) = $result->fetch(3)) {
        $row['linkmd5'][] = $linkmd5;
    }
    $row['linkmd5_old'] = $row['linkmd5'];
} else {
    $row['id'] = 0;
    $row['idsender'] = 0;
    $row['idreplyto'] = 0;
    $row['title'] = '';
    $row['content'] = '';
    $row['files'] = '';
    $row['usergroup'] = array();
    $row['customergroup'] = array();
    $row['emaillist'] = '';
    $row['typetime'] = 0;
    $row['begintime'] = NV_CURRENTTIME;
    $row['endtime'] = 0;
    $row['linkstatics'] = 1;
    $row['openstatics'] = 1;
    $row['linkmd5'] = $row['linkmd5_old'] = array();
}

$row['redirect'] = $nv_Request->get_title('redirect', 'get', '');

if ($nv_Request->isset_request('submit', 'post') or $draft) {
    $row['title'] = $nv_Request->get_title('title', 'post', '');
    $row['content'] = $nv_Request->get_editor('content', '', NV_ALLOWED_HTML_TAGS);
    $row['files'] = $nv_Request->get_title('files', 'post', '');
    $row['usergroup'] = $nv_Request->get_typed_array('usergroup', 'post', 'int');
    $row['customergroup'] = $nv_Request->get_typed_array('customergroup', 'post', 'int');
    $row['emaillist'] = $nv_Request->get_textarea('emaillist', '', 0, 1);
    $row['totalemailsend'] = $nv_Request->get_int('totalemailsend', 'post', 0);
    $row['totalmailsuccess'] = $nv_Request->get_int('totalmailsuccess', 'post', 0);
    $row['typetime'] = $nv_Request->get_int('typetime', 'post', 0);
    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $nv_Request->get_string('begintime', 'post'), $m)) {
        $_hour = $nv_Request->get_int('begintime_hour', 'post');
        $_min = $nv_Request->get_int('begintime_min', 'post');
        $row['begintime'] = mktime($_hour, $_min, 59, $m[2], $m[1], $m[3]);
    } else {
        $row['begintime'] = 0;
    }
    $row['linkstatics'] = $nv_Request->get_int('linkstatics', 'post', 0);
    $row['openstatics'] = $nv_Request->get_int('openstatics', 'post', 0);
    $row['idsender'] = $nv_Request->get_int('idsender', 'post', 0);
    $row['idreplyto'] = $nv_Request->get_int('idreplyto', 'post', 0);

    if (!$draft) {
        if (empty($row['content'])) {
            $error[] = $lang_module['error_required_content'];
        } elseif (empty($row['idsender'])) {
            $error[] = $lang_module['error_required_sender'];
        }
        $row['sendstatus'] = 0;
    } else {
        $row['sendstatus'] = 2;
    }

    $is_vaild = 0;
    if (!empty($row['usergroup'])) {
        $is_vaild = 1;
        $row['usergroup'] = serialize($row['usergroup']);
    } else {
        $row['usergroup'] = '';
    }

    if (!empty($row['customergroup'])) {
        $is_vaild = 1;
        $row['customergroup'] = serialize($row['customergroup']);
    } else {
        $row['customergroup'] = '';
    }

    if (!empty($row['emaillist'])) {
        $is_vaild = 1;
        $row['emaillist'] = explode('<br />', $row['emaillist']);
        foreach ($row['emaillist'] as $index => $email) {
            if (!empty($email)) {
                if ($check = nv_check_valid_email($email)) {
                    if (!empty($check)) {
                        $error[] = '<strong>[' . $email . ']</strong> ' . $check;
                    }
                }
            } else {
                unset($row['emaillist'][$index]);
            }
        }

        if (!empty($row['emaillist'])) {
            $row['emaillist'] = implode('<br />', $row['emaillist']);
        }
    }

    if (!$is_vaild) {
        $error[] = $lang_module['error_required_email'];
    }

    if (empty($error)) {
        try {
            $new_id = 0;

            // Kiem tra link trong noi dung
            if ($row['linkstatics']) {
                $array_link = array();
                $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
                if (preg_match_all("/$regexp/siU", $row['content'], $matches)) {
                    foreach ($matches[2] as $index => $link) {
                        $linkmd5 = md5($link);
                        $array_link[$index] = array(
                            'text' => $matches[3][$index],
                            'link' => $link,
                            'linkmd5' => $linkmd5
                        );
                        $row['linkmd5'][$index] = $linkmd5;
                    }
                }
            }

            if (empty($row['id'])) {
                $_sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_rows (idsender, idreplyto, title, content, files, usergroup, customergroup, emaillist, addtime, typetime, begintime, endtime, linkstatics, openstatics, sendstatus) VALUES (:idsender, :idreplyto, :title, :content, :files, :usergroup, :customergroup, :emaillist, ' . NV_CURRENTTIME . ', :typetime, :begintime, :endtime, :linkstatics, :openstatics, :sendstatus)';
                $data_insert = array();
                $data_insert['idsender'] = $row['idsender'];
                $data_insert['idreplyto'] = $row['idreplyto'];
                $data_insert['title'] = $row['title'];
                $data_insert['content'] = $row['content'];
                $data_insert['files'] = $row['files'];
                $data_insert['usergroup'] = $row['usergroup'];
                $data_insert['customergroup'] = $row['customergroup'];
                $data_insert['emaillist'] = $row['emaillist'];
                $data_insert['typetime'] = $row['typetime'];
                $data_insert['begintime'] = $row['begintime'];
                $data_insert['endtime'] = $row['endtime'];
                $data_insert['linkstatics'] = $row['linkstatics'];
                $data_insert['openstatics'] = $row['openstatics'];
                $data_insert['sendstatus'] = $row['sendstatus'];
                $new_id = $db->insert_id($_sql, 'id', $data_insert);
            } else {
                $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET idsender = :idsender, idreplyto = :idreplyto, title = :title, content = :content, files = :files, usergroup = :usergroup, customergroup = :customergroup, emaillist = :emaillist, begintime = :begintime, typetime = :typetime, endtime = :endtime, linkstatics = :linkstatics, openstatics = :openstatics, sendstatus = :sendstatus WHERE id=' . $row['id']);
                $stmt->bindParam(':idsender', $row['idsender'], PDO::PARAM_INT);
                $stmt->bindParam(':idreplyto', $row['idreplyto'], PDO::PARAM_INT);
                $stmt->bindParam(':title', $row['title'], PDO::PARAM_STR, strlen($row['title']));
                $stmt->bindParam(':content', $row['content'], PDO::PARAM_STR, strlen($row['content']));
                $stmt->bindParam(':files', $row['files'], PDO::PARAM_STR, strlen($row['files']));
                $stmt->bindParam(':usergroup', $row['usergroup'], PDO::PARAM_STR, strlen($row['usergroup']));
                $stmt->bindParam(':customergroup', $row['customergroup'], PDO::PARAM_STR, strlen($row['customergroup']));
                $stmt->bindParam(':emaillist', $row['emaillist'], PDO::PARAM_STR, strlen($row['emaillist']));
                $stmt->bindParam(':typetime', $row['typetime'], PDO::PARAM_INT);
                $stmt->bindParam(':begintime', $row['begintime'], PDO::PARAM_INT);
                $stmt->bindParam(':endtime', $row['endtime'], PDO::PARAM_INT);
                $stmt->bindParam(':linkstatics', $row['linkstatics'], PDO::PARAM_INT);
                $stmt->bindParam(':openstatics', $row['openstatics'], PDO::PARAM_INT);
                $stmt->bindParam(':sendstatus', $row['sendstatus'], PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $new_id = $row['id'];
                }
            }

            if ($new_id > 0) {

                if ($row['linkstatics']) {
                    if ($row['linkmd5'] != $row['linkmd5_old']) {
                        $sth = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link (rowsid, text, linkmd5, link) VALUES(:rowsid, :text, :linkmd5, :link)');
                        foreach ($row['linkmd5'] as $index => $linkmd5) {
                            $text = strip_tags($array_link[$index]['text']);
                            $text = !empty($text) ? $text : $lang_module['link'];
                            if (!in_array($linkmd5, $row['linkmd5_old'])) {
                                $sth->bindParam(':rowsid', $new_id, PDO::PARAM_INT);
                                $sth->bindParam(':text', $text, PDO::PARAM_STR);
                                $sth->bindParam(':linkmd5', $linkmd5, PDO::PARAM_STR);
                                $sth->bindParam(':link', $array_link[$index]['link'], PDO::PARAM_STR);
                                $sth->execute();
                            }
                        }

                        foreach ($row['linkmd5_old'] as $linkmd5_old) {
                            if (!in_array($linkmd5_old, $row['linkmd5'])) {
                                $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link WHERE linkmd5 = ' . $db->quote($linkmd5_old) . ' AND rowsid=' . $new_id);
                            }
                        }
                    }
                }

                $nv_Cache->delMod($module_name);

                if (!empty($row['redirect'])) {
                    $url = nv_redirect_decrypt($row['redirect']);
                } elseif ($row['typetime'] == 0) {
                    $url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=send&id=' . $new_id;
                } else {
                    $url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name;
                }

                Header('Location: ' . $url);
                die();
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
        }
    }
}

if (empty($row['begintime'])) {
    $row['begintimef'] = '';
} else {
    $row['begintimef'] = date('d/m/Y', $row['begintime']);
}

if (!empty($row['emaillist'])) {
    $row['emaillist'] = nv_br2nl($row['emaillist']);
}

if (defined('NV_EDITOR')) require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
$row['content'] = htmlspecialchars(nv_editor_br2nl($row['content']));
if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
    $row['content'] = nv_aleditor('content', '100%', '300px', $row['content']);
} else {
    $row['content'] = '<textarea style="width:100%;height:300px" name="content">' . $row['content'] . '</textarea>';
}

$row['style_begintime'] = $row['typetime'] == 0 ? 'style="display: none"' : '';
$row['ck_linkstatics'] = $row['linkstatics'] ? 'checked="checked"' : '';
$row['ck_openstatics'] = $row['openstatics'] ? 'checked="checked"' : '';

$sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_sender WHERE status=1 ORDER BY weight';
$array_sender = $nv_Cache->db($sql, 'id', $module_name);

$row['emaillist_disabled'] = defined('NV_CUSTOMER') ? 'disabled="disabled"' : '';

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('OP', $op);
$xtpl->assign('ROW', $row);

$row['customergroup'] = !empty($row['customergroup']) ? unserialize($row['customergroup']) : array();
if (!empty($array_customer_groups)) {
    foreach ($array_customer_groups as $customergroups) {
        $customergroups['checked'] = in_array($customergroups['id'], $row['customergroup']) ? 'checked="checked"' : '';
        $xtpl->assign('CUSGROUP', $customergroups);
        $xtpl->parse('main.customergroup');
    }
}

$array_usergroup = nv_groups_list();
$row['usergroup'] = !empty($row['usergroup']) ? unserialize($row['usergroup']) : array();
if (!empty($array_usergroup)) {
    foreach ($array_usergroup as $groupid => $title) {
        $ck = in_array($groupid, $row['usergroup']) ? 'checked="checked"' : '';
        $xtpl->assign('USERGROUP', array(
            'id' => $groupid,
            'title' => $title,
            'checked' => $ck
        ));
        $xtpl->parse('main.usergroup');
    }
}

$array_typetime = array(
    0 => $lang_module['typetime_0']
);

foreach ($array_typetime as $index => $value) {
    $ck = $index == $row['typetime'] ? 'checked="checked"' : '';
    $xtpl->assign('TYPETIME', array(
        'index' => $index,
        'value' => $value,
        'checked' => $ck
    ));
    $xtpl->parse('main.typetime');
}

$hour = !empty($row['begintime']) ? date('H', $row['begintime']) : 0;
for ($i = 0; $i <= 23; $i++) {
    $sl = $i == $hour ? 'selected="selected"' : '';
    $xtpl->assign('HOUR', array(
        'index' => $i,
        'selected' => $sl
    ));
    $xtpl->parse('main.hour');
}

$min = !empty($row['begintime']) ? date('i', $row['begintime']) : 0;
for ($i = 0; $i <= 59; $i++) {
    $sl = $i == $min ? 'selected="selected"' : '';
    $xtpl->assign('MIN', array(
        'index' => $i,
        'selected' => $sl
    ));
    $xtpl->parse('main.min');
}

if (!empty($array_sender)) {
    foreach ($array_sender as $sender) {
        $sender['selected'] = $sender['id'] == $row['idsender'] ? 'selected="selected"' : '';
        $xtpl->assign('SENDER', $sender);
        if (!empty($sender['name'])) {
            $xtpl->parse('main.sender.name');
        }
        $xtpl->parse('main.sender');
    }

    foreach ($array_sender as $replyto) {
        $replyto['selected'] = $replyto['id'] == $row['idreplyto'] ? 'selected="selected"' : '';
        $xtpl->assign('REPLYTO', $replyto);
        if (!empty($replyto['name'])) {
            $xtpl->parse('main.replyto.name');
        }
        $xtpl->parse('main.replyto');
    }
}

if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['campaign_add'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';