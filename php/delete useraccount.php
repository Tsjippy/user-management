<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

//Delete user shortcode
add_shortcode('tsjippy_delete_user', __NAMESPACE__ . '\deleteUser');
function deleteUser()
{
    require_once(ABSPATH . 'wp-admin/includes/user.php');

    $user = wp_get_current_user();

    if (!in_array('usermanagement', $user->roles)) {
        return "<div class='error'>You have no permission to delete user accounts!</div>";
    }

    //Load js
    wp_enqueue_script('user_select_script');

    $html = "";

    if (isset($_GET["user-id"])) {
        $userId   = (int) $_GET["user-id"];
        $userdata = get_userdata($userId);
        if (!$userdata) {
            return "<div class='error'>User with id $userId does not exist.</div>";
        }

        $family         = get_user_meta($userId, "tsjippy_family", true);

        if (!isset($_GET["confirm"])) {
            $html .= askConfirmation($userdata, $family);
        } elseif ($_GET["confirm"] == "true") {
            $html .= removeUserAccount( $family, $userdata, $userId);
        }
    }

    $html .= TSJIPPY\userSelect("Select an user to delete from the website:");

    return $html;
}

function askConfirmation($userdata, $family)
{
    $html    = "<script>";
    $html    .= "var remove = confirm('Are you sure you want to remove the useraccount for $userdata->display_name?');";
    $html    .= "if (remove) {";
    $html    .= "var url=`\${window.location}&nonce=" . wp_create_nonce('delete_user_' . $userdata->ID . '_nonce') . "`;";
    if (is_array($family) && !empty($family)) {
        $html    .= "var family = confirm('Do you want to delete all useraccounts for the familymembers of $userdata->display_name as well?');";
        $html    .= "if (family) {";
        $html    .= "window.location = url+'&confirm=true&family=true'";
        $html    .= "}else{";
        $html    .= "window.location = url+'&confirm=true'";
        $html    .= "}";
    } else {
        $html    .= "window.location = url+'&confirm=true'";
    }
    $html    .= "}";
    $html    .= "</script>";

    return $html;
}

function removeUserAccount($family, $userdata)
{
    $html     = '';
    $family = new TSJIPPY\FAMILY\Family();

    if (!TSJIPPY\verifyNonce('nonce', 'delete_user_' . $userdata->ID . '_nonce')) {
        $html .= '<div class="error">Invalid nonce! Refresh the page</div>';
    } else {
        $deletedName = $userdata->display_name;
        if (($_GET["family"] ?? '') == "true" && is_array($family) && !empty($family)) {
            $deletedName .= " and all the family";

            foreach ($family->getFamily($userdata->ID, true) as $relative) {
                //Remove user account
                wp_delete_user($relative, 1);
            }
        }
        //Remove user account
        wp_delete_user($userdata->ID, 1);
        $html .= "<div class='success'>Useraccount for $deletedName succcesfully deleted.</div>";
        $html .= "<script>";
        $html .= "setTimeout(function () {";
        $html .= "window.location = window.location.href.replace('/?user-id=$userdata->ID&nonce=" . TSJIPPY\sanitize($_GET['delete_user_' . $userdata->ID . '_nonce']) . "&confirm=true','').replace('&family=true','');";
        $html .= "}, 3000);";
        $html .= "</script>";
    }

    return $html;
}
