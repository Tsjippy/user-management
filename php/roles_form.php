<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

/**
 * Creates the form to edit a users roles
 *
 * @param    int        $userId
 *
 * @return    string            The form html
 */
function displayRoles($userId = '')
{
    global $wp_roles;

    wp_enqueue_script('tsjippy_user_management');

    $roles    = [];

    //Get all available roles
    $userRoles    = $wp_roles->role_names;

    //Sort the roles
    asort($userRoles);

    if (is_numeric($userId)) {
        $user   = get_userdata($userId);

        if(!$user){
            return;
        }

        //Get the roles this user currently has
        $roles         = array_flip($user->roles);

        //Remove these roles from the roles array
        if (!isset($roles['administrator'])) {
            unset($userRoles['administrator']);
        }
    }

    wp_enqueue_style('tsjippy_useraccount');

    //Content
?>

    <div class="role-info">
        <?php
        if (wp_is_mobile()) {
            foreach ($userRoles as $key => $roleName) {
                $checked = '';
                if (
                    isset($roles[$key]) ||
                    (
                        empty($userId)    &&
                        $key    == 'revisor'
                    )
                ) {
                    $checked = 'checked';
                }
        ?>
                <label>
                    <input type='checkbox' name='roles[<?php echo esc_attr($key); ?>]' value='<?php echo esc_attr($roleName); ?>' <?php echo esc_attr($checked); ?>>
                    <?php
                    echo esc_attr($roleName);
                    ?>
                    <div class="infobox">
                        <div class="info-icon-wrapper">
                            <p class="info-icon">
                                <img draggable="false" role="img" class="emoji" alt="ℹ" loading='lazy' src="<?php echo TSJIPPY\PICTURESURL; ?>/info.png">
                            </p>
                        </div>
                        <span class="info-text">
                            <?php
                            echo esc_attr($roleName) . ' - <i>' . esc_html(apply_filters('tsjippy-user-management-role-description', '', $key)) . '</i>';
                            ?>
                        </span>
                    </div>
                </label>
                <br>
            <?php
            }
        } else {
            ?>
            <table class="no-border" style='width: max-content;'>
                <?php
                foreach ($userRoles as $key => $roleName) {
                    $checked = '';
                    if (
                        isset($roles[$key]) ||
                        (
                            empty($userId)    &&
                            $key    == 'revisor'
                        )
                    ) {
                        $checked = 'checked';
                    }
                ?>
                    <tr>
                        <td>
                            <label>
                                <input type='checkbox' name='roles[<?php echo esc_attr($key); ?>]' value='<?php echo esc_attr($roleName); ?>' <?php echo esc_attr($checked); ?>>
                                <?php
                                echo esc_attr($roleName);
                                ?>
                            </label>
                        </td>
                        <td>
                            <i>
                                <?php
                                echo wp_kses_post(apply_filters('tsjippy-user-management-role-description', '', $key));
                                ?>
                            </i>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </table>
        <?php
        }
        ?>
    </div>
<?php
}
