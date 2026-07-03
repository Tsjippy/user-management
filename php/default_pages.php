<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

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
        $settings['account_page']           = TSJIPPY\ADMIN\createDefaultPage('Account', '[tsjippy_user-info currentuser=true]');
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
        $settings['pending-users-page']     = TSJIPPY\ADMIN\createDefaultPage('Pending user accounts', '[tsjippy_pending_user]');
    }

    update_option('tsjippy_' . PLUGINSLUG . '_settings', $settings);

    if(!empty($returnKey) && isset($settings[$returnKey])){
        return $settings[$returnKey];
    }
}