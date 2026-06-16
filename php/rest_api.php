<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;
use WP_User;

add_action('rest_api_init', __NAMESPACE__ . '\restApiInit');
function restApiInit()
{
    // disable or enable useraccount
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/user_management',
        '/disable-user-account',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\disableUserAccount',
            'permission_callback'     => function () {
                return in_array('usermanagement', wp_get_current_user()->roles);
            },
            'args'                    => array(
                'user-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($userId) {
                        return is_numeric($userId);
                    }
                )
            )
        )
    );

    // update user roles
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/user_management',
        '/update_roles',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     function ($wp_rest_request) {
                return updateRoles($_REQUEST['user-id'], $_REQUEST['roles']);
            },
            'permission_callback'     => function () {
                return (bool)array_intersect(['usermanagement', 'administrator'], wp_get_current_user()->roles);
            },
            'args'                    => array(
                'user-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($userId) {
                        return is_numeric($userId);
                    }
                ),
                'roles'        => array(
                    'required'    => true
                )
            )
        )
    );

    // add user account
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/user_management',
        '/add_useraccount',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     function () {
                return TSJIPPY\createUserAccount(false);
            },
            'permission_callback'     => '__return_true', // Allow non-logged in users to access this endpoint, as this is used for self-registration
            'args'                    => array(
                'first-name' => array(
                    'required'    => true
                ),
                'last-name'     => array(
                    'required'    => true
                )
            )
        )
    );

    // extend user account validity
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/user_management',
        '/extend_validity',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\extendValidity',
            'permission_callback'     => function () {
                return in_array('usermanagement', wp_get_current_user()->roles);
            },
            'args'                    => array(
                'user-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($userId) {
                        return is_numeric($userId);
                    }
                ),
                'new-expiry-date'        => array(
                    'required'    => true
                )
            )
        )
    );

    // get userpage tab contents
    register_rest_route(
        TSJIPPY\RESTAPIPREFIX . '/user_management',
        '/get_userpage_tab',
        array(
            'methods'                 => 'POST',
            'callback'                 =>     __NAMESPACE__ . '\getUserPageTab',
            'permission_callback'     => function () {
                return current_user_can('read');
            },
            'args'                    => array(
                'user-id'        => array(
                    'required'    => true,
                    'validate_callback' => function ($userId) {
                        return is_numeric($userId);
                    }
                ),
                'tabname'        => array(
                    'required'    => true
                )
            )
        )
    );
}

/**
 * Get the content for a specific user page tab
 *
 * @param \WP_REST_Request $wpRestRequest The REST request containing the user ID and tab name
 *
 * @return array An array containing the HTML, JS, and CSS for the requested tab content
 */
function getUserPageTab($wpRestRequest)
{
    $params                = $wpRestRequest->get_params();

    $userId                = $params['user-id'];

    $genericInfoRoles     = array_merge(['usermanagement'], ['administrator']);
    $userSelectRoles    = apply_filters('tsjippy-user-page-dropdown', $genericInfoRoles);
    $user                 = wp_get_current_user();
    $userRoles             = $user->roles;

    if ($userId    != get_current_user_id() && array_intersect($userSelectRoles, $userRoles)) {
        $admin    = true;
    } else {
        $admin    = false;
    }

    switch ($params['tabname']) {
        case 'generic':
            $html    = getGenericsTab($userId);
            break;
        case 'dashboard':
            $html    = showDashboard($userId, $admin);
            break;
        case 'family':
            $html    = do_shortcode("[tsjippy_formbuilder slug=user_family user-id='$userId']");
            break;
        case 'location':
            $html    = do_shortcode("[tsjippy_formbuilder slug=user_location user-id='$userId']");
            break;
        case 'profile-picture':
            $html    = do_shortcode("[tsjippy_formbuilder slug=profile_picture user-id='$userId']");
            break;
        case 'security':
            $html    = do_shortcode("[tsjippy_formbuilder slug=security_questions user-id='$userId']");
            break;
        default:
            // check if tabname has a number
            $childId    = explode('-', $params['tabname']);
            if ($childId[0] == 'child' && isset($childId[1]) && is_numeric($childId[1])) {
                $html    = showChildrenFields($childId[1]);
            } else {
                $html    = "<div class='error'>Something went wrong, you should never see this</div>";
            }
    }

    do_action('wp_enqueue_scripts');
    ob_start();
    wp_print_scripts();
    $js    = ob_get_clean();

    do_action('wp_enqueue_style');
    ob_start();
    wp_print_styles();
    $css    = ob_get_clean();

    return [
        'html'    => $html,
        'js'    => $js,
        'css'    => $css
    ];
}

function disableUserAccount()
{
    if (empty(get_user_meta((int) $_POST['user-id'], 'tsjippy_disabled', true))) {
        update_user_meta((int) $_POST['user-id'], 'tsjippy_disabled', true);
        return 'Succesfully disabled the user account';
    } else {
        delete_user_meta((int) $_POST['user-id'], 'tsjippy_disabled');
        return 'Succesfully enabled the user account';
    }
}

/**
 * Update the users roles
 */
add_action('tsjippy-after-user-register', __NAMESPACE__ . '\updateRoles');
function updateRoles($userId = '', $newRoles = [])
{
    if (!function_exists('populate_roles')) {
        require_once(ABSPATH . 'wp-admin/includes/schema.php');
    }

    populate_roles();

    if (empty($userId)) {
        $userId    = $_POST['user-id'];
    }

    $user         = get_userdata($userId);
    if (!$user) {
        return new \WP_Error('user', 'No user found');
    }

    $userRoles     = $user->roles;

    if (empty($newRoles)) {
        $newRoles    = TSJIPPY\sanitize((array)$_POST['roles']);
    }

    if (empty(array_diff($userRoles, array_keys($newRoles))) && empty(array_diff(array_keys($newRoles), $userRoles))) {
        return "Nothing to update";
    }

    TSJIPPY\saveExtraUserRoles($userId, $newRoles);

    return "Updated roles succesfully";
}

/**
 * Extend the validity of an temporary account
 */
function extendValidity()
{
    $userId = $_POST['user-id'];
    if (($_POST['unlimited'] ?? '') == 'unlimited') {
        $date       = 'unlimited';
        $message    = "Marked the useraccount for " . get_userdata($userId)->first_name . " to never expire. ";
    } else {
        $date       = TSJIPPY\sanitize($_POST['new-expiry-date']);
        $dateStr   = gmdate(TSJIPPY\DATEFORMAT, strtotime($date));
        $message    = "Extended valitidy for " . get_userdata($userId)->first_name . " till $dateStr";
    }
    update_user_meta($userId, 'tsjippy_account_validity', $date);

    return $message;
}
