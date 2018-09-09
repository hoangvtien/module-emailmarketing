<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Tue, 08 Nov 2016 01:39:51 GMT
 */
if (!defined('NV_IS_FILE_ADMIN')) die('Stop!!!');

$id = $nv_Request->get_int('id', 'post,get', 0);
$array_email = array();

if (!empty($id)) {
    $rows = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id=' . $id)->fetch();
    if ($rows) {

        if ($nv_Request->isset_request('send_test_email', 'post')) {
            $title = nv_build_title($rows['title'], array(), true);
            $content = nv_build_content($id, $rows['content'], array(), 0, 0, true);
            nv_emailmarketing_sendmail($id, $global_config['site_email'], $global_config['site_email'], $title, $content);
            die($lang_module['send_test_email_success']);
        }

        $rows['sendlist'] = !empty($rows['sendlist']) ? explode(',', $rows['sendlist']) : array();
        $rows['sendlist'] = array_map('intval', $rows['sendlist']);
        $rows['sendedlist'] = !empty($rows['sendedlist']) ? explode(',', $rows['sendedlist']) : array();
        $rows['sendedlist'] = array_map('intval', $rows['sendedlist']);
        $rows['errorlist'] = !empty($rows['errorlist']) ? explode(',', $rows['errorlist']) : array();
        $rows['errorlist'] = array_map('intval', $rows['errorlist']);
        $rows['openedlist'] = !empty($rows['openedlist']) ? explode(',', $rows['openedlist']) : array();
        $rows['openedlist'] = array_map('intval', $rows['openedlist']);

        if ($nv_Request->isset_request('send', 'post')) {
            $array_send = array_merge($rows['sendedlist'], $rows['errorlist']);
            $array_send = array_diff($rows['sendlist'], $array_send);
            asort($array_send);
            if (!empty($array_send)) {
                foreach ($array_send as $index => $customerid) {
                    if (defined('NV_CUSTOMER')) {
                        require_once NV_ROOTDIR . '/modules/customer/site.functions.php';
                        $result = nv_crm_customer_info($customerid);
                        $result['email'] = $result['main_email'];
                    } else {
                        $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE id=' . $customerid)->fetch();
                    }
                    if ($result) {
                        $customer = array(
                            'customerid' => $result['id'],
                            'nextcustomerid' => isset($array_send[$index + 1]) ? $array_send[$index + 1] : 0,
                            'email' => $result['email'],
                            'fullname' => $result['fullname'],
                            'gender' => $result['gender']
                        );
                        die(nv_sendmail_action($customer, $rows));
                    }
                }
            } else {
                die(json_encode(array(
                    'status' => 'exit'
                )));
            }
        }

        if ($nv_Request->isset_request('openstatics', 'post,get')) {

            $array_data = array(
                array(
                    'name' => $lang_module['openmail_1'],
                    'y' => (count($rows['openedlist']) * 100) / count($rows['sendlist']),
                    'x' => count($rows['openedlist']),
                    'openedlist' => $rows['openedlist'],
                    'title' => $lang_module['emaillist_opendedlist_1']
                ),
                array(
                    'name' => $lang_module['openmail_0'],
                    'y' => ((count($rows['sendlist']) - count($rows['openedlist'])) * 100) / count($rows['sendlist']),
                    'x' => count($rows['sendlist']) - count($rows['openedlist']),
                    'openedlist' => array_values(array_diff($rows['sendlist'], $rows['openedlist'])),
                    'title' => $lang_module['emaillist_opendedlist_0']
                )
            );

            die(json_encode($array_data));
        }

        if ($nv_Request->isset_request('linkstatics', 'post,get')) {
            $array_link = array();
            $result = $db->query('SELECT countclick, listclick FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link WHERE rowsid=' . $id);
            $i = 0;
            while (list ($countclick, $listclick) = $result->fetch(3)) {
                $array_link[] = array(
                    'index' => $i,
                    'countclick' => $countclick,
                    'listclick' => $listclick
                );
                $i++;
            }

            die(json_encode($array_link));
        }

        $lang_module['send'] = sprintf($lang_module['sendmail_s'], $rows['title']);

        // Cap nhat danh sach khach hang nhan mail
        if ($rows['sendstatus'] == 0) {
            $array_email = nv_listmail_content($id);

            $sendlist = array();
            foreach ($array_email as $data) {
                $sendlist[] = nv_check_customer($data);
            }

            $count = count($sendlist);
            if ($rows['sendlist'] != $sendlist) {
                asort($sendlist);
                $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET sendlist=' . $db->quote(implode(',', $sendlist)) . ', totalemailsend = ' . $count . ' WHERE id=' . $id);
                $rows['sendlist'] = $sendlist;
            }
        }

        // Danh sach link trong noi dung
        $array_link = array();
        if ($rows['linkstatics']) {
            $result = $db->query('SELECT text, link, countclick FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link WHERE rowsid=' . $id);
            while ($_row = $result->fetch()) {
                $array_link[] = $_row;
            }
        }
    } else {
        die(json_encode(array(
            'status' => 'exit'
        )));
    }
} else {
    Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
    die();
}

$lang_module['send_test_email_confirm'] = sprintf($lang_module['send_test_email_confirm'], $global_config['site_email']);

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('TEMPLATE', $global_config['module_theme']);

$array_email = array();
if (!empty($rows['sendlist'])) {
    if (defined('NV_CUSTOMER')) {
        $result = $db->query('SELECT id, main_email email FROM ' . NV_PREFIXLANG . '_customer WHERE id IN(' . implode(',', $rows['sendlist']) . ') ORDER BY id');
    } else {
        $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE id IN(' . implode(',', $rows['sendlist']) . ') ORDER BY id');
    }
    while ($_row = $result->fetch()) {
        $array_email[] = $_row;
    }
}

if (!empty($array_email)) {
    $number = 1;
    $firstcustomerid = 0;
    foreach ($array_email as $data) {
        if ($number == 1) {
            $firstcustomerid = $data['id'];
        }
        $data['number'] = $number++;
        if (in_array($data['id'], $rows['sendedlist'])) {
            $data['sendstatus'] = 1;
        } elseif (in_array($data['id'], $rows['errorlist'])) {
            $data['sendstatus'] = 3;
        } else {
            $data['sendstatus'] = 0;
        }
        $data['sendstatus_str'] = $lang_module['sendstatus_' . $data['sendstatus']];
        $xtpl->assign('DATA', $data);

        if ($data['sendstatus'] == 1) {
            $xtpl->parse('main.loop.sendsuccess');
        } elseif ($data['sendstatus'] == 3) {
            $xtpl->parse('main.loop.senderror');
        }

        $xtpl->parse('main.loop');
    }
    $xtpl->assign('ROWSID', $id);
    $xtpl->assign('TOTAL', count($array_email));
    $xtpl->assign('TOTALSENDER', count($rows['sendedlist']));
    $xtpl->assign('PERCENT', (count($rows['sendedlist']) * 100) / count($array_email));
    $xtpl->assign('FIRSTCUSTOMERID', $firstcustomerid);
    $xtpl->assign('COUNTSUCCESS', count($rows['sendedlist']));
    $xtpl->assign('COUNTERROR', count($rows['errorlist']));

    if (!empty($rows['sendedlist']) and $rows['sendstatus'] == 1) {
        $nextcustomerid = end($rows['sendedlist']);
        $nextcustomerid = array_search($nextcustomerid, $rows['sendlist']);
        $nextcustomerid = isset($rows['sendlist'][$nextcustomerid + 1]) ? $rows['sendlist'][$nextcustomerid + 1] : 'undefined';
    } else {
        $nextcustomerid = 'undefined';
    }
    $xtpl->assign('NEXTCUSTOMERID', $nextcustomerid);

    if ($rows['sendstatus'] == 0) {
        $xtpl->parse('main.btn_control');
    }

    if ($rows['openstatics']) {
        $xtpl->parse('main.openstatics');
    }

    if ($rows['linkstatics'] and !empty($array_link)) {
        $number = 1;
        foreach ($array_link as $index => $link) {
            $link['number'] = $number++;
            $link['index'] = $index;
            $xtpl->assign('LINK', $link);
            $xtpl->parse('main.linkstatics.loop');
        }
        $xtpl->parse('main.linkstatics');
    }
}

$array_button = array(
    'edit' => NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=content&amp;id=' . $rows['id'] . '&amp;redirect=' . nv_redirect_encrypt($client_info['selfurl']),
    'delete' => NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=main&amp;delete_id=' . $rows['id'] . '&amp;delete_checkss=' . md5($rows['id'] . NV_CACHE_PREFIX . $client_info['session_id'])
);
$xtpl->assign('BUTTON', $array_button);

if ($rows['sendstatus'] == 1) {
    $xtpl->parse('main.sendstatus_disabled');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$set_active_op = 'content';
$page_title = $lang_module['send'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';