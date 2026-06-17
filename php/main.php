<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

add_filter('display_post_states', __NAMESPACE__ . '\postStates', 10, 2);
function postStates($states, $post)
{

    if ($post->ID == (SETTINGS['account_page'] ?? '')) {
        $states[] = __('Account page', '%TEXTDOMAIN%');
    } elseif ($post->ID == (SETTINGS['user-edit-page'] ?? '')) {
        $states[] = __('User edit page', '%TEXTDOMAIN%');
    } elseif ($post->ID == (SETTINGS['account-create-page'] ?? '')) {
        $states[] = __('Account create page', '%TEXTDOMAIN%');
    } elseif ($post->ID == (SETTINGS['pending-users-page'] ?? '')) {
        $states[] = __('Pending users page', '%TEXTDOMAIN%');
    }

    return $states;
}

add_filter('tsjippy-user-management-role-description', __NAMESPACE__ . '\roleDescription', 10, 2);
function roleDescription($description, $role)
{
    if ($role == 'rolemanagement') {
        return 'Ability to grant people an extra role';
    }
    if ($role == 'usermanagement') {
        return 'Ability to edit other user accounts';
    }
    return $description;
}
