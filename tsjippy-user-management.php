<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

/**
 * Plugin Name:          Tsjippy User Management
 * Description:          This plugin adds 5 shortcodes: <h4>user-info</h4> This shortcode displays all forms to set and update userdata.< You can also change userdata for other users if you have the 'usermanagement' role. Use like this: <code>[user-info currentuser='true']</code> <h4>userstatistics</h4> This shortcode displays a table listing all website users and some of their details. Use like this: <code>[userstatistics]</code> <h4>create_user_account</h4> This shortcode displays a from to create new user accounts. Use like this: <code>[create_user_account]</code> <h4>pending_user</h4> This shortcode displays all user account who are pending approval. Use like this: <code>[pending_user]</code> <h4>change_password</h4>This shortcode displays a form for users to reset their password. Use like this: <code>[change_password]</code>
 * Version:              10.5.5
 * Author:               Ewald Harmsen
 * AuthorURI:            harmseninnigeria.nl
 * Requires at least:    6.3
 * Requires PHP:         8.3
 * Tested up to:         7.0
 * Plugin URI:            https://github.com/Tsjippy/usermanagement
 * Tested:               7.0
 * TextDomain:            tsjippy
 * Requires Plugins:    , tsjippy-forms
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @author Ewald Harmsen
 */
if (! defined('ABSPATH')) {
    exit;
}

// Load shared code
if(file_exists(__DIR__  . '/shared-functionality/loader.php')){
    require_once(__DIR__  . '/shared-functionality/loader.php');
}

// Define constants
define(__NAMESPACE__ . '\PLUGIN', plugin_basename(__FILE__));
define(__NAMESPACE__ . '\PLUGINPATH', __DIR__ . '/');
define(__NAMESPACE__ . '\PLUGINVERSION', get_plugin_data(__FILE__, false, false)['Version']);
define(__NAMESPACE__ . '\PLUGINSLUG', str_replace('tsjippy-', '', basename(__FILE__, '.php')));
define(__NAMESPACE__ . '\SETTINGS', get_option('tsjippy_user-management_settings', []));

// run right before activation
register_activation_hook(__FILE__, function () {
    // Load shared code
    if(file_exists(__DIR__  . '/shared-functionality/loader.php')){
        require_once(__DIR__  . '/shared-functionality/loader.php');
    }
    
    createDefaultPages();

    /**
     * Import the forms
     */
    $formBuilder    = new \TSJIPPY\FORMS\FormExport();

    $files = glob(PLUGINPATH  . "imports/*.sform");
    foreach ($files as $file) {
        $formBuilder->importForm($file);
    }

    // add the last logindate for existing users
    foreach (get_users(['meta_key' => 'tsjippy_last_login_date', 'meta_compare'  => 'NOT EXISTS']) as $user) {
        update_user_meta($user->ID, 'tsjippy_last_login_date', gmdate('Y-m-d'));
    }

    /**
     * Add roles
     */
    // Only add the new role if it does not exist
    if (!wp_roles()->is_role('rolemanagement')) {
        $roleSet                  = get_role('contributor')->capabilities;
        $roleSet['edit_users']    = true;
        $roleSet['list_users']    = true;
        $roleSet['promote_users'] = true;

        add_role(
            'rolemanagement',
            'Role Manager',
            $roleSet
        );
    }

    if (!wp_roles()->is_role('usermanagement')) {
        $roleSet                  = get_role('contributor')->capabilities;
        $roleSet['edit_users']    = true;
        $roleSet['list_users']    = true;
        $roleSet['remove_users']  = true;
        $roleSet['promote_users'] = true;

        add_role(
            'usermanagement',
            'User Manager',
            $roleSet
        );
    }

    if(function_exists('TSJIPPY\activate')){
        \TSJIPPY\activate();
    }
});

// run on deactivation
register_deactivation_hook(__FILE__, function () {

    // Remove the auto created pages
    foreach (['2fa-page', 'user-edit-page', 'account-create-page', 'pending-users-page'] as $page) {
        if (is_numeric(SETTINGS[$page] ?? false))
            // Remove the auto created page
            wp_delete_post(SETTINGS[$page], true);
    }
});

/**
 * Creates default pages if needed
 */
function createDefaultPages($returnKey=''){
    /**
     *  Default pages
     */
    $settings    = SETTINGS;

    // Create account page
    if(!isset($settings['account_page'])){
        $settings['account_page']           = TSJIPPY\ADMIN\createDefaultPage('Account', 'tsjippy_user-info currentuser=true]');
    }

    if(!isset($settings['user-edit-page'])){
        // Create user edit page
        $settings['user-edit-page']         = TSJIPPY\ADMIN\createDefaultPage('Edit users', '[tsjippy_user-info]');
    }

    if(!isset($settings['account-create-page'])){
        // Create user create page
        $settings['account-create-page']    = TSJIPPY\ADMIN\createDefaultPage('Add user account', '[tsjippy_create_user_account]');
    }

    if(!isset($settings['pending-users-page'])){
        // Create pending users page
        $settings['pending-users-page']     = TSJIPPY\ADMIN\createDefaultPage('Pending user accounts', '<!-- wp:tsjippy-user-management/pending-user-accounts /-->');
    }

    update_option('tsjippy_' . PLUGINSLUG . '_settings', $settings);

    if(!empty($returnKey) && isset($settings[$returnKey])){
        return $settings[$returnKey];
    }
}