<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

// add extra question to the new user form
add_action('tsjippy-user-management-after-user-create-form', __NAMESPACE__ . '\afterUserCreateForm');
function afterUserCreateForm()
{
    ?>
    <label>
        <h4>
            User roles<span class="required">*</span>
        </h4>
    </label>
    <?php
    displayRoles();
}

// store the results of the form above
add_action('tsjippy-user-management-approved-user', __NAMESPACE__ . '\userApproved');
function userApproved($userId)
{
    update_user_meta($userId, 'tsjippy_visa_info', TSJIPPY\sanitize($_POST['visa-info']));
}
