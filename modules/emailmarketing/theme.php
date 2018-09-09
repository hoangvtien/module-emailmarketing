<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */
if (!defined('NV_IS_MOD_EMAILMARKETING')) die('Stop!!!');

/**
 * nv_theme_emailmarketing_main()
 *
 * @param mixed $array_data            
 * @return
 *
 */
function nv_theme_emailmarketing_main($array_data)
{
    global $global_config, $module_name, $module_file, $lang_module, $module_config, $module_info, $op;
    
    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    
    $xtpl->parse('main');
    return $xtpl->text('main');
}