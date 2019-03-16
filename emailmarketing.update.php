<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2015 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Wed, 02 Dec 2015 08:26:04 GMT
 */
define('NV_SYSTEM', true);

// Xac dinh thu muc goc cua site
define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));

require NV_ROOTDIR . '/includes/mainfile.php';
require NV_ROOTDIR . '/includes/core/user_functions.php';

// Duyệt tất cả các ngôn ngữ
$language_query = $db->query('SELECT lang FROM ' . $db_config['prefix'] . '_setup_language WHERE setup = 1');
while (list ($lang) = $language_query->fetch(3)) {
    // Duyet nvfaqs va module ao
    $mquery = $db->query("SELECT title, module_data FROM " . $db_config['prefix'] . "_" . $lang . "_modules WHERE module_file = 'emailmarketing'");
    while (list ($mod, $mod_data) = $mquery->fetch(3)) {

        $_sql = array();

        $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . " CHANGE fullname first_name VARCHAR(100) NOT NULL;";

        $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . " ADD last_name VARCHAR(150) NOT NULL AFTER first_name;";

        $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_tmp CHANGE fullname first_name VARCHAR(100) NOT NULL;";

        $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_tmp ADD last_name VARCHAR(150) NOT NULL AFTER first_name;";

        $db->query("UPDATE " . $db_config['prefix'] . "_setup_extensions SET version='1.0.01 " . NV_CURRENTTIME . "' WHERE type='module' and basename=" . $db->quote($mod));

        if (!empty($_sql)) {
            foreach ($_sql as $sql) {
                try {
                    $db->query($sql);
                } catch (PDOException $e) {
                    //
                }
            }
            $nv_Cache->delMod($mod);
            $nv_Cache->delMod('settings');
        }
    }
}

die('OK');