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
        //Get the roles this user currently has
        $roles         = get_userdata($userId)->roles;

        //Remove these roles from the roles array
        if (!in_array('administrator', (array)$roles)) {
            unset($userRoles['administrator']);
        }
    }

    ob_start();
    //Content
?>
    <style>
        .role-info .infobox {
            margin-top: -20px;
        }

        .role-info .info-icon-wrapper {
            margin-bottom: 10px;
        }

        .role-info .info-icon {
            margin-bottom: 0px;
            position: absolute;
            right: 10px;
            max-width: 20px;
        }

        .role-info .infobox .info-text {
            position: absolute;
            right: 40px;
            bottom: unset;
            display:none;
        }

        .role-info .infobox:hover{
            margin-top: 0px;
        }
        .role-info .infobox:hover .info-text {
            display: inline-block;
        }
    </style>

    <div class="role-info">
        <?php
        if (wp_is_mobile()) {
            foreach ($userRoles as $key => $roleName) {
                $checked = '';
                if (
                    in_array($key, (array)$roles) ||
                    (
                        empty($userId)    &&
                        $key    == 'revisor'
                    )
                ) {
                    $checked = 'checked';
                }
        ?>
                <label>
                    <input type='checkbox' name='roles[<?php echo esc_attr($key); ?>]' value='<?php echo esc_attr($roleName); ?>' <?php echo $checked; ?>>
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
                            echo esc_attr($roleName) . ' - <i>' . esc_html(apply_filters('tsjippy_role_description', '', $key)) . '</i>';
                            ?>
                        </span>
                    </div>
                </label>
                <br>
            <?php
            }
        } else {
            ?>
            <table style='border: none; width: max-content;'>
                <?php
                foreach ($userRoles as $key => $roleName) {
                    $checked = '';
                    if (
                        in_array($key, (array)$roles) ||
                        (
                            empty($userId)    &&
                            $key    == 'revisor'
                        )
                    ) {
                        $checked = 'checked';
                    }
                ?>
                    <tr style='border: none;'>
                        <td style='border: none;'>
                            <label>
                                <input type='checkbox' name='roles[<?php echo esc_attr($key); ?>]' value='<?php echo esc_attr($roleName); ?>' <?php echo $checked; ?>>
                                <?php
                                echo $roleName;
                                ?>
                            </label>
                        </td>
                        <td style='border: none;'>
                            <i>
                                <?php
                                echo apply_filters('tsjippy_role_description', '', $key);
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
    return ob_get_clean();
}
