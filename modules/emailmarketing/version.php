<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */
if (!defined('NV_MAINFILE')) die('Stop!!!');

$module_version = array(
    'name' => 'Emailmarketing',
    'modfuncs' => 'main,statics,send',
    'change_alias' => 'main,statics',
    'submenu' => 'main',
    'is_sysmod' => 0,
    'virtual' => 0,
    'version' => '1.0.00',
    'date' => 'Sat, 15 Oct 2016 03:30:10 GMT',
    'author' => 'mynukeviet (contact@mynukeviet.net)',
    'uploads_dir' => array(
        $module_name
    ),
    'note' => ''
);