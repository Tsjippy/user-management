<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

/**
 * Shows the user account dashboard of a user
 *
 * @param    int        $userId        WP_User id
 * @param    bool    $admin        Whether we run this for an admin account, default false
 *
 * @return    string                The dashboard html
 */
function showDashboard($userId, $admin = false)
{
    if (!is_numeric($userId)) {
        return "<p>Invalid user id $userId</p>";
    };

    ob_start();
    $userdata  = get_userdata($userId);
    $firstName = $userdata->first_name ?? $userdata->display_name;

    if ($admin) {
        $loginCount = get_user_meta($userId, 'tsjippy_login_count', true);
        $lastLogin  = get_user_meta($userId, 'tsjippy_last_login_date', true);

        //show last login date
        ?>
        <p id='login-message' style='border: 3px solid #bd2919; padding: 10px; text-align: center;'>
            <?php
            if (is_numeric($loginCount)) {
                $timeString     = strtotime($lastLogin);
                if ($timeString) {
                    $lastLogin = gmdate('d F Y', $timeString);
                }

                echo esc_html($firstName);?> has logged in <?php echo esc_html($loginCount);?> times.<br>Last login was <?php echo esc_html($lastLogin);?>
                <?php
            } else {
                echo esc_html($firstName);?> has never logged in.<br>
                <?php
            }
            ?>
        </p>
        <?php
    }

    ?>
    <p>
        Hello <?php echo esc_html($firstName);?>
    </p>
    <div id="warnings" style="padding: 20px 0;">
        <?php
        $dashboardWarnings    = new DashboardWarnings($userId);

        if (!empty($dashboardWarnings->reminderHtml)) {
            $text    = 'Reminders';

            if ($dashboardWarnings->reminderCount < 2) {
                $dashboardWarnings->reminderHtml = str_replace(['</li>', '<li>'], '', $dashboardWarnings->reminderHtml);
                $text    = 'Reminder';
            } else {
                $dashboardWarnings->reminderHtml = str_replace(['</li>','<li>'], '', $dashboardWarnings->reminderHtml);
            }

            ?>
            <div id=reminders>
                <h5 class='frontpage'><?php echo esc_attr($text); ?></h5>
                <p>
                    <?php echo $dashboardWarnings->reminderHtml; ?>
                </p>
            </div>
            <?php
        }
        
        do_action('tsjippy-user-management-dashboard-warnings', $userId, $admin);
        ?>
    </div>

    <?php
    do_action('tsjippy-user-management-dashboard', $userId, $admin);

    return ob_get_clean();
}
