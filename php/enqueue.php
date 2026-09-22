<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\loadAssets', 99);
function loadAssets()
{
    /**
     * CSS
     */
    wp_register_style('tsjippy_useraccount', TSJIPPY\pathToUrl(PLUGINPATH . 'css/account.min.css'), array(), PLUGINVERSION);

    /**
     * Scripts
     */
    $deps   = SCRIPT_DEBUG ? [  
        '@tsjippy/form_submit_functions',
        "@tsjippy/display_message"
    ] :
    [];
    wp_register_script_module('@tsjippy/user_management', TSJIPPY\pathToUrl(PLUGINPATH . 'js/user_management' . TSJIPPY\JSEXTENSION), $deps, PLUGINVERSION);

    $deps   = SCRIPT_DEBUG ? [  
        '@tsjippy/form_submit_functions', 
        "@tsjippy/load_assets", 
        "@tsjippy/show_loader"
    ] :
    [];
    wp_register_script_module('@tsjippy/userpage', TSJIPPY\pathToUrl(PLUGINPATH . 'js/userpage' . TSJIPPY\JSEXTENSION), $deps, PLUGINVERSION);
}
