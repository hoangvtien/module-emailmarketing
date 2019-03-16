<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */
if (!defined('NV_ADMIN') or !defined('NV_MAINFILE') or !defined('NV_IS_MODADMIN')) die('Stop!!!');

define('NV_IS_FILE_ADMIN', true);
require_once NV_ROOTDIR . '/modules/' . $module_file . '/global.functions.php';

$allow_func = array(
    'main',
    'config',
    'customer',
    'customer-groups',
    'customer-content',
    'content',
    'dielist',
    'sender',
    'declined',
    'send',
    'mailserver',
    'customer-detail'
);

$array_gender = array(
    1 => $lang_module['gender_1'],
    0 => $lang_module['gender_0'],
    2 => $lang_module['gender_2']
);

$array_customer_groups[0] = array(
    'id' => 0,
    'title' => $lang_module['undefine'],
    'weight' => 0,
    'status' => 1
);

if (defined('NV_CUSTOMER')) {
    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_customer_types WHERE active=1 ORDER BY weight';
    $array_customer_groups += $nv_Cache->db($sql, 'id', 'customer');
} else {
    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_groups WHERE status=1 ORDER BY weight';
    $array_customer_groups += $nv_Cache->db($sql, 'id', $module_name);
}

function nv_emailmarketing_customer_delete($id)
{
    global $db, $module_data;

    $count = $db->exec('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer WHERE id =' . $id);
    if ($count) {
        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_customer_groups WHERE customerid =' . $id);
    }
}

function nv_row_delete($id)
{
    global $db, $module_data;

    $count = $db->exec('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id =' . $id);
    if ($count) {
        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows_link WHERE rowsid =' . $id);
    }
}

function nv_emailmarketing_check_mobile($phone)
{
    global $lang_module;
    if (!preg_match("/^[0-9]{10,11}$/", $phone)) {
        return $lang_module['mobile_is_error'];
    }
}


function nv_users_add($username, $password, $email, $first_name, $last_name, $gender, $birthday = 0, $adduser_email = 1)
{
    global $db, $global_config, $user_info, $nv_Cache, $crypt, $lang_module;
    
    // chế độ import dữ liệu
    $groups_list = nv_groups_list();
    
    $_user = array();
    $_user['view_mail'] = 0;
    $_user['in_groups'] = array(
        4 // thành viên chính thức
    );
    $_user['in_groups_default'] = 0;
    $_user['is_official'] = 1;
    
    // xác định nhóm thành viên
    $in_groups = array();
    foreach ($_user['in_groups'] as $_group_id) {
        if ($_group_id > 9) {
            $in_groups[] = $_group_id;
        }
    }
    $_user['in_groups'] = array_intersect($in_groups, array_keys($groups_list));
    
    if (empty($_user['is_official'])) {
        $_user['in_groups'][] = 7;
        $_user['in_groups_default'] = 7;
    } elseif (empty($_user['in_groups_default']) or !in_array($_user['in_groups_default'], $_user['in_groups'])) {
        $_user['in_groups_default'] = 4;
    }
    
    if (empty($_user['in_groups_default']) and sizeof($_user['in_groups'])) {
        trigger_error($lang_module['edit_error_group_default']);
        return 0;
    }
    
    $sql = "INSERT INTO " . NV_USERS_GLOBALTABLE . " (
                    group_id, username, md5username, password, email, first_name, last_name, gender, birthday, sig, regdate,
                    question, answer, passlostkey, view_mail,
                    remember, in_groups, active, checknum, last_login, last_ip, last_agent, last_openid, idsite)
                VALUES (
                    " . $_user['in_groups_default'] . ",
                    :username,
                    :md5_username,
                    :password,
                    :email,
                    :first_name,
                    :last_name,
                    :gender,
                    " . $birthday . ",
                    :sig,
                    " . NV_CURRENTTIME . ",
                    :question,
                    :answer,
                    '',
                     " . $_user['view_mail'] . ",
                     1,
                     '" . implode(',', $_user['in_groups']) . "', 1, '', 0, '', '', '', " . $global_config['idsite'] . "
                )";
    $data_insert = array();
    $data_insert['username'] = $username;
    $data_insert['md5_username'] = nv_md5safe($username);
    $data_insert['password'] = $crypt->hash_password($password, $global_config['hashprefix']);
    $data_insert['email'] = $email;
    $data_insert['first_name'] = $first_name;
    $data_insert['last_name'] = $last_name;
    $data_insert['gender'] = $gender;
    $data_insert['sig'] = '';
    $data_insert['question'] = '';
    $data_insert['answer'] = '';
    $userid = $db->insert_id($sql, 'userid', $data_insert);
    
    if (!$userid) {
        trigger_error($lang_module['error_unknow']);
        return 0;
    }    
    
    if (!empty($_user['in_groups'])) {
        foreach ($_user['in_groups'] as $group_id) {
            if ($group_id != 7) {
                nv_groups_add_user($group_id, $userid, 1, $module_data);
            }
        }
    }
    $db->query('UPDATE ' . NV_USERS_GLOBALTABLE . '_groups SET numbers = numbers+1 WHERE group_id=' . ($_user['is_official'] ? 4 : 7));
    $nv_Cache->delMod('users');
    
    // Gửi mail thông báo
    if (!empty($adduser_email)) {
        $full_name = nv_show_name_user($first_name, $last_name, $username);
        $subject = $lang_module['adduser_register'];
        $_url = NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=users', true);
        $message = sprintf($lang_module['adduser_register_info1'], $full_name, $global_config['site_name'], $_url, $username, $password);
        @nv_sendmail($global_config['site_email'], $email, $subject, $message);
    }
    
    return $userid;
}

/**
 * nv_groups_list()
 *
 * @return
 */


/**
 * nv_emailmatketing_download()
 *
 * @param mixed $array_data
 * @param mixed $type
 * @return
 *
 */
function nv_emailmatketing_download($title, $array_data, $type = 'xlsx')
{
    global $module_name, $admin_info, $lang_module, $workforce_list, $user_info, $array_field_config;
    
    if (empty($array_data)) {
        die('Nothing download!');
    }
    $array = array(
        'objType' => '',
        'objExt' => ''
    );
    switch ($type) {
        case 'xlsx':
            $array['objType'] = 'Excel2007';
            $array['objExt'] = 'xlsx';
            break;
        case 'ods':
            $array['objType'] = 'OpenDocument';
            $array['objExt'] = 'ods';
            break;
        default:
            $array['objType'] = 'CSV';
            $array['objExt'] = 'csv';
    }
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    // Set properties
    $objPHPExcel->getProperties()
    ->setCreator($admin_info['username'])
    ->setLastModifiedBy($admin_info['username'])
    ->setTitle($title)
    ->setSubject($title)
    ->setDescription($title)
    ->setCategory($module_name);
    $columnIndex = 0; // Cot bat dau ghi du lieu
    $rowIndex = 3; // Dong bat dau ghi du lieu
    // thông tin thành viên
    $objPHPExcel->getActiveSheet()
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex) . $rowIndex, $lang_module['number'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 1) . $rowIndex, $lang_module['fullname'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 2) . $rowIndex, $lang_module['gender'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 3) . $rowIndex, $lang_module['birthday'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 4) . $rowIndex, $lang_module['phone'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 5) . $rowIndex, $lang_module['email'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 6) . $rowIndex, $lang_module['customer_groups'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 7) . $rowIndex, $lang_module['addtime'])
    ->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnIndex + 8) . $rowIndex, $lang_module['status']);
    
    // Hiển thị thông tin dữ liệu
    $i = $rowIndex + 1;
    $number = 1;
    foreach ($array_data as $data) {
        // số thứ tự
        $col = PHPExcel_Cell::stringFromColumnIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $number);
        // thông tin thành viên
        $col = PHPExcel_Cell::stringFromColumnIndex(1);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['fullname']);
        $col = PHPExcel_Cell::stringFromColumnIndex(2);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['gender']);
        $col = PHPExcel_Cell::stringFromColumnIndex(3);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['birthday']);
        $col = PHPExcel_Cell::stringFromColumnIndex(4);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['phone']);
        $col = PHPExcel_Cell::stringFromColumnIndex(5);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['email']);
        $col = PHPExcel_Cell::stringFromColumnIndex(6);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['customer_groups']);
        $col = PHPExcel_Cell::stringFromColumnIndex(7);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['addtime']);
        $col = PHPExcel_Cell::stringFromColumnIndex(8);
        $objPHPExcel->getActiveSheet()->setCellValue($col . $i, $data['status']);
        
        // thông tin tùy biến
        $j = $columnIndex + 9;
        
        
        $i++;
        $number++;
    }
    $highestRow = $i - 1;
    $highestColumn = PHPExcel_Cell::stringFromColumnIndex($j - 1);
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Sheet 1');
    // Set page orientation and size
    $objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
    $objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    // Excel title
    $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $highestColumn . '2');
    $objPHPExcel->getActiveSheet()->setCellValue('A2', $title);
    $objPHPExcel->getActiveSheet()
    ->getStyle('A2')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()
    ->getStyle('A2')
    ->getAlignment()
    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    // Cấu hình hiển thị trong excel
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array(
                    'argb' => 'FF000000'
                )
            )
        )
    );
    $objPHPExcel->getActiveSheet()
    ->getStyle('A3' . ':' . $highestColumn . $highestRow)
    ->applyFromArray($styleArray);
    
    // Set font size
    $objPHPExcel->getActiveSheet()
    ->getStyle("A1:" . $highestColumn . $highestRow)
    ->getFont()
    ->setSize(13);
    
    $styleArray = array(
        'font' => array(
            'bold' => true
        )
    );
    $objPHPExcel->getActiveSheet()
    ->getStyle('A2')
    ->applyFromArray($styleArray);
    
    // Set font size
    $objPHPExcel->getActiveSheet()
    ->getStyle("A2")
    ->getFont()
    ->setSize(16);
    
    $objPHPExcel->getActiveSheet()
    ->getStyle("A3:" . $highestColumn . 3)
    ->getFont()
    ->setBold(true);
    
    // Set auto column width
    foreach (range('A', $highestColumn) as $columnID) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($columnID)
        ->setAutoSize(true);
    }
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $array['objType']);
    $file_src = NV_ROOTDIR . NV_BASE_SITEURL . NV_TEMP_DIR . '/' . change_alias($lang_module['post_list'] . '-' . nv_date('d/m/Y', NV_CURRENTTIME)) . '.' . $array['objExt'];
    $objWriter->save($file_src);
    
    $download = new NukeViet\Files\Download($file_src, NV_ROOTDIR . NV_BASE_SITEURL . NV_TEMP_DIR);
    $download->download_file();
    die();
}
