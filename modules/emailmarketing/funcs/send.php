<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */
if (!defined('NV_IS_MOD_EMAILMARKETING')) die('Stop!!!');

$mod = $nv_Request->get_title('mod', 'get', '');

if ($mod == 'campaign') {

    set_time_limit(0);

    $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE sendstatus=0 AND typetime=1 AND begintime<=' . NV_CURRENTTIME);
    while ($rows = $result->fetch()) {
        $array_email = nv_listmail_content($rows['id']);

        $sendlist = array();
        foreach ($array_email as $data) {
            $sendlist[] = nv_check_customer($data);
        }

        $count = count($sendlist);
        if ($rows['sendlist'] != $sendlist) {
            asort($sendlist);
            $sendlist = implode(',', $sendlist);
            $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET sendlist=' . $db->quote($sendlist) . ', totalemailsend = ' . $count . ' WHERE id=' . $rows['id']);
            $rows['sendlist'] = $sendlist;
        }

        $rows['sendlist'] = !empty($rows['sendlist']) ? explode(',', $rows['sendlist']) : array();
        $rows['sendlist'] = array_map('intval', $rows['sendlist']);
        $rows['sendedlist'] = !empty($rows['sendedlist']) ? explode(',', $rows['sendedlist']) : array();
        $rows['sendedlist'] = array_map('intval', $rows['sendedlist']);
        $rows['errorlist'] = !empty($rows['errorlist']) ? array_map('intval', explode(',', $rows['errorlist'])) : array();

        $array_send = array_merge($rows['sendedlist'], $rows['errorlist']);
        $array_send = !empty($array_send) ? $array_send : array();
        $array_send = array_diff($rows['sendlist'], $array_send);

        if (!empty($array_send)) {
            $i = 1;
            asort($array_send);
            foreach ($array_send as $index => $customerid) {
                if ($i <= $array_config['numsend']) {
                    if (defined('NV_CUSTOMER')) {
                        require_once NV_ROOTDIR . '/modules/customer/site.functions.php';
                        $customer = nv_crm_customer_info($customerid);
                        $customer['email'] = $customer['main_email'];
                    } else {
                        $customer = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE id=' . $customerid)->fetch();
                    }

                    if ($customer) {
                        list ($rows['sendedlist'], $rows['errorlist']) = $db->query('SELECT sendedlist, errorlist FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id=' . $rows['id'])->fetch(3);
                        $rows['sendedlist'] = !empty($rows['sendedlist']) ? explode(',', $rows['sendedlist']) : array();
                        $rows['sendedlist'] = array_map('intval', $rows['sendedlist']);
                        $rows['errorlist'] = !empty($rows['errorlist']) ? explode(',', $rows['errorlist']) : array();
                        $rows['errorlist'] = array_map('intval', $rows['errorlist']);

                        $_customer = array(
                            'customerid' => $customer['id'],
                            'nextcustomerid' => isset($array_send[$index + 1]) ? $array_send[$index + 1] : 0,
                            'email' => $customer['email'],
                            'fullname' => $customer['fullname'],
                            'gender' => $customer['gender']
                        );
                        nv_sendmail_action($_customer, $rows, 0);
                        $i++;
                    }
                } else {
                    break;
                }
            }
        }
    }
} elseif ($mod == 'queue') {

    set_time_limit(0);

    $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_emailmarketing_queue');
    while ($row = $result->fetch()) {
        $from = $row['from_email'];
        if (!empty($row['from_name'])) {
            $from = array(
                $row['from_name'],
                $row['from_email']
            );
        }

        nv_emailmarketing_sendmail(0, $from, $row['to_email'], $row['subject'], $row['message'], $row['to_email'], $row['files']);

        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_emailmarketing_queue WHERE id=' . $row['id']);
    }
}
