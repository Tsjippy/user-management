<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

//add_shortcode("tsjippy_userstatistics", __NAMESPACE__ . '\userStatistics');
function userStatistics()
{
    add_filter('tsjippy-frontend-content-post-edit-button', function ($buttonHtml, $post, $content) {
        return $buttonHtml . "<form style='display: inline-block;' action='' method='post'><button class='button small' name='getlist' value=1>Get Contact List</button></form>";
    }, 10, 3);

    if (isset($_REQUEST['getlist'])) {
        TSJIPPY\USERPAGES\buildUserDetailPdf('screen');
        return;
    }

    wp_enqueue_script('tsjippy_table_script');

    ob_start();

    $users         = TSJIPPY\getUserAccounts(false, true);

    $baseUrl    = get_permalink(SETTINGS['user-edit-page'] ?? '');
    if (!$baseUrl) {
        $baseUrl = '';
    }

    ?>
    <br>
    <div class='table-wrapper'>
        <table class='tsjippy table no-border' style='max-height:500px;'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Login count</th>
                    <th>Last login</th>
                    <th>Mandatory pages to read</th>
                    <th>User roles</th>
                    <th>Account validity</th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($users as $user) {
                    $loginCount = get_user_meta($user->ID, 'tsjippy_login_count', true);
                    if (!is_numeric(($loginCount))) {
                        $loginCount = 0;
                    }

                    $lastLoginDate    = get_user_meta($user->ID, 'tsjippy_last_login_date', true);
                    if (empty($lastLoginDate)) {
                        $lastLoginDate    = 'Never';
                    } else {
                        $timeString     = strtotime($lastLoginDate);
                        if ($timeString) {
                            $lastLoginDate = gmdate('d F Y', $timeString);
                        }
                    }
                    ?>

                    <tr class='table-row'>
                        
                        <td>
                            <?php TSJIPPY\displayProfilePicture(userId: $user->ID, echo: true);?> 
                            <a href='<?php echo esc_url($baseUrl);?>/?user-id=<?php echo esc_attr($user->ID);?>'>
                                <?php echo esc_html($user->display_name);?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($loginCount);?>
                        </td>
                        <td>
                            <?php echo esc_html($lastLoginDate);?>
                        </td>
                        <?php 
                        if (function_exists('TSJIPPY\MANDATORY\mustReadDocuments')) {
                            ?>
                            <td>
                                <?php echo esc_html(TSJIPPY\MANDATORY\mustReadDocuments($user->ID, true, true) );?>
                            </td>
                        <?php
                        }
                        ?>
                        <td>
                            <?php
                                foreach ($user->roles as $role) {
                                    echo esc_attr($role) . '<br>';
                                }
                                ?>
                        </td>
                        <td>
                            <?php echo esc_html(get_user_meta($user->ID, 'tsjippy_account_validity', true));?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
    return ob_get_clean();
}
