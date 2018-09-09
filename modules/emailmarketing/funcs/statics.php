<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */
if (!defined('NV_IS_MOD_EMAILMARKETING')) die('Stop!!!');

$rowsid = $nv_Request->get_int('rowsid', 'get', 0);
$action = $nv_Request->get_title('action', 'get', '');
$idcustomer = $nv_Request->get_int('customer', 'get', 0);
$checksum = $nv_Request->get_title('checksum', 'get', '');

if (!empty($idcustomer) and $checksum == md5($global_config['sitekey'] . '-' . $idcustomer . '-' . $rowsid)) {
    if ($action == 'openlink') {
        $link = $nv_Request->get_title('link', 'get', '');
        $linkmd5 = $nv_Request->get_title('linkmd5', 'get', '');
        $row = $db->query('SELECT link, listclick FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link WHERE linkmd5=' . $db->quote($linkmd5))
            ->fetch();
        if ($row and !empty($linkmd5)) {
            $listclick = !empty($row['listclick']) ? explode(',', $row['listclick']) : array();
            if (!in_array($idcustomer, $listclick)) {
                $listclick[] = $idcustomer;
                $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link SET listclick=' . $db->quote(implode(',', $listclick)) . ', countclick=' . count($listclick) . ' WHERE linkmd5=' . $db->quote($linkmd5) . ' AND rowsid=' . $rowsid);
            }
            Header('Location: ' . $row['link']);
            die();
        } elseif (!empty($link)) {
            Header('Location: ' . base64_decode($link));
            die();
        } else {
            die('Wrong URL!');
        }
    } elseif ($action == 'openmail') {
        $row = $db->query('SELECT openedlist FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id=' . $rowsid)->fetch();
        if ($row) {
            $openedlist = !empty($row['openedlist']) ? explode(',', $row['openedlist']) : array();
            $openedlist = array_map('intval', $openedlist);
            if (!in_array($idcustomer, $openedlist)) {
                $openedlist[] = $idcustomer;
                $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET openedlist=' . $db->quote(implode(',', $openedlist)) . ' WHERE id=' . $rowsid);
            }
        }
    } elseif ($action == 'declined') {
        if ($array_config['allow_declined']) {
            $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_customer SET is_declined=1 WHERE id=' . $idcustomer);
            $lang_module['declined_content'] = sprintf($lang_module['declined_content'], $global_config['site_name']);
            $contents = nv_theme_alert($lang_module['decline_title'], $lang_module['decline_content'], 'info', NV_BASE_SITEURL, $lang_module['gohome']);
        } else {
            $contents = nv_theme_alert($lang_module['declined_error_title'], $lang_module['declined_error_content'], 'warning');
        }
        
        include NV_ROOTDIR . '/includes/header.php';
        echo nv_site_theme($contents);
        include NV_ROOTDIR . '/includes/footer.php';
    }
} else {
    Header('Location: ' . NV_BASE_SITEURL);
    die();
}