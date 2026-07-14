<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

add_action('init', __NAMESPACE__ . '\scheduleTasks');
/**
 * Schedule all tasks for this plugin
 */
function scheduleTasks()
{
    TSJIPPY\scheduleTask('tsjippy-user-management-birthday-check', 'daily', __NAMESPACE__, 'birthdayCheck');
    TSJIPPY\scheduleTask('tsjippy-user-management-account-expiry-check', 'daily', __NAMESPACE__, 'accountExpiryCheck');
    TSJIPPY\scheduleTask('tsjippy-user-management-check-last-login-date', 'monthly', __NAMESPACE__, 'checkLastLoginDate');

    $freq    = SETTINGS['check-details-mail-freq'] ?? false;
    if ($freq) {
        TSJIPPY\scheduleTask('tsjippy-user-management-check-details-mail', $freq, __NAMESPACE__, 'checkDetailsMail');
    }
}

/**
 * Finds all users that have their birthday today
 */
function birthdayCheck()
{
    //Current date time
    $date   = new \DateTime();

    //Get all the birthday users of today
    $users = get_users(array(
        'meta_key'     => 'tsjippy_birthday',
        'meta_value'   => $date->format('-m-d'),
        'meta_compare' => 'LIKE',
    ));

    foreach ($users as $user) {
        $userId     = $user->ID;
        $firstName     = $user->first_name;

        $family = new TSJIPPY\FAMILY\Family();

        //Send birthday wish to the user
        add_action(
            'tsjippy-user-management-birthday-message',
            "Hi $firstName,\nCongratulations with your birthday!",
            $userId
        );

        //Send to parents
        if ($family->isChild($userId)) {
            $childTitle = TSJIPPY\getChildTitle($user->ID);

            $message = "Congratulations with the birthday of your $childTitle " . get_userdata($user->ID)->first_name;

            foreach ($family->getParents($userId) as $parent) {
                add_action(
                    'tsjippy-user-management-birthday-message',
                    "Hi " . get_userdata($parent)->first_name . ",\n$message",
                    $parent
                );
            }
        }
    }
}

/**
 * send an e-mail with an overview of an users details for them to check
 */
function checkDetailsMail()
{

    $family     = new TSJIPPY\FAMILY\Family();

    $subject    = 'Please review your website profile';

    //Retrieve all users
    $users             = TSJIPPY\getUserAccounts(false, true);

    $accountPageUrl    = get_permalink(SETTINGS['account_page'] ?? createDefaultPages('account_page'));

    $baseUrl           = "$accountPageUrl?main-tab=";

    //Loop over the users
    foreach ($users as $user) {
        ob_start();
        
        //Send e-mail
        ?>
        <style>
            .colored{
                text-decoration: none; 
                color: #444;
            }
        </style>
        Hi <?php echo esc_attr($user->first_name);?>,<br>
        <br>        
        Once a year we would like to remind you to keep your information on the website up to date.<br>
        Please check the information below to see if it is still valid, if not update it.<br>
        <br>

        <!-- PROFILE PICTURE -->
        <a href='<?php echo esc_url($baseUrl);?>profilePicture' class='colored'>
            <b>
                Profile picture
            </b>
        </a>
        <br>

        <?php
        $profilePicture    = getProfilePictureUrl($user->ID);
        if ($profilePicture) {
            ?>
            This is your profile picture:<br>
            <img src='<?php echo esc_url($profilePicture);?>' alt='<?php echo esc_url($profilePicture);?>' width='100px' height='100px'>
            <br>
            <br>
            <?php
        } else {
            ?>
            <table>
                <tr>
                    <td>
                        <a href='<?php echo esc_url($baseUrl);?>profilePicture' class='colored'>
                            You have not uploaded a picture
                        </a>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
        <br>

        <!-- PERSONAL DETAILS /-->
        <a href='<?php echo esc_url($baseUrl);?>generic-info' class='colored'>
            <b>
                Personal details
            </b>
        </a>
        <br>

        <table>
            <tr>
                <td>
                    Name:
                </td>
                <td>
                    <?php echo esc_attr($user->display_name);?>
                </td>
            </tr>
            <tr>
                <td>
                    Birthday:
                </td>
                <td>
                    <a href='<?php echo esc_url($baseUrl);?>generic-info#birthday' class='colored'>
                        <?php
                        $birthday = get_user_meta($user->ID, 'tsjippy_birthday', true);
                        if (empty($birthday)) {
                            ?>
                            No birthday specified.
                            <?php
                        } else {
                            echo gmdate('d  F Y', strtotime($birthday));
                        }
                        ?>
                    </a>
                </td>
            </tr>

            <?php
            do_action('tsjippy-user-management-inside-personal-details-table', $user, $baseUrl);
            ?>
        </table>
        <br>

        <!-- PHONENUMBERS -->

        <?php
        $phonenumbers = get_user_meta($user->ID, 'tsjippy_phonenumbers');
        array_filter($phonenumbers);

        ?>
        <a href='<?php echo esc_url($baseUrl);?>generic-info' class='colored'>
            <b>
                Phonenumber<?php if (count($phonenumbers) > 1) echo( 's'); ?>
            </b>
        </a>
        <br>
        <table>
            <?php
            if (empty($phonenumbers)) {
                ?>
                <tr>
                    <td>
                        <a href='<?php echo esc_url($baseUrl);?>generic-info#phonenumbers[0]' class='colored'>
                            No phonenumbers provided
                        </a>
                    </td>
                </tr>
                <?php
            } elseif (count($phonenumbers) == 1) {
                ?>
                <tr>
                    <td>
                        <a href='<?php echo esc_url($baseUrl);?>generic-info#phonenumbers[0]' class='colored'>
                            <?php echo wp_kses_post(array_values($phonenumbers)[0]);?>
                        </a>
                    </td>
                </tr>
                <?php
            } else {
                foreach ($phonenumbers as $key => $number) {
                    $nr    = $key + 1;
                    ?>
                    <tr>
                        <td>
                            Phonenumber <?php echo esc_attr($nr);?>:
                        </td>
                        <td>
                            <a href='<?php echo esc_url($baseUrl);?>generic-info#phonenumbers[<?php echo esc_attr($key);?>]' class='colored'>
                                <?php echo esc_attr($number);?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
        <br>

        <!-- MINISTRIES -->
        <a href='<?php echo esc_url($baseUrl);?>generic-info' class='colored'>
            <b>
                <?php
                $userMinistries = (array)get_user_meta($user->ID, 'tsjippy_jobs', true);
                array_filter($userMinistries);
                if (count($userMinistries) > 1) {
                    ?>Ministries<?php
                } else {
                    ?>Ministry<?php
                }
                ?>
            </b>
        </a>
        <br>

        <table>
            <?php
            if (empty($userMinistries)) {
                ?>
                <tr>
                    <td>
                        <a href='<?php echo esc_url($baseUrl);?>generic-info#ministries[]' class='colored'>
                            No ministry provided
                        </a>
                    </td>
                </tr>
                <?php
            } else {
                foreach ($userMinistries as $ministry => $job) {
                    ?>
                    <tr>
                        <td>
                            <?php echo get_the_title($ministry)?>:
                        </td>
                        <td>
                            <a href='<?php echo esc_url($baseUrl);?>generic-info#ministries[]' class='colored'>
                                <?php echo esc_html($job);?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
        <br>

        <!-- LOCATION -->
        <a href='<?php echo esc_url($baseUrl);?>location' class='colored'>
            <b>
                Location
            </b>
        </a>
        <br>

        <table>
            <tr>
                <td>
                    <a href='<?php echo esc_url($baseUrl);?>location#location[compound]' class='colored'>
                        <?php
                        $location    = (array)get_user_meta($user->ID, 'tsjippy_location', true);
                        array_filter($location);
                        if (empty($location['address'])) {
                            ?>No location provided<?php
                        } else {
                            echo esc_attr($location['address']);
                        }
                        ?>
                    </a>
                </td>
            </tr>
        </table>
        <br>
        <?php
        /*
        ** FAMILY
         */
        $partner   = $family->getPartner($user->ID, true);
        $children  = $family->getChildren($user->ID);
        $siblings  = $family->getSiblings($user->ID);
        if ($partner || $children || $siblings) {
            $picture    = $family->getFamilyMeta($partner, 'family_picture', true);
            if ($picture) {
                $url        = wp_get_attachment_url($picture);
                ?><img src='<?php echo esc_url($url);?>' width=100 height=100><?php
            } else {
                ?>You have not uploaded a picture<?php
            }
            ?>

            <a href='<?php echo esc_url($baseUrl);?>family' class='colored'>
                <b>
                    Family details
                </b>
            </a>
            <br>
            <table>
                <tr>
                    <td>
                        Family picture:
                    </td>
                    <td>
                        <a href='<?php echo esc_url($baseUrl);?>family#family_picture' class='colored'>
                            <?php echo esc_attr($picture);?>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td>
                        Spouse:
                    </td>
                    <td>
                        <a href='<?php echo esc_url($baseUrl);?>family#partner' class='colored'>
                            <?php 
                            if ($partner) {
                                echo esc_attr($partner->display_name);
                            }else{
                                ?>You have no spouse<?php
                            }
                            ?>
                        </a>
                    </td>
                </tr>
                <?php
                if ($partner) {
                    ?>
                    <tr>
                        <td>
                            Wedding date:
                        </td>
                        <td>
                            <a href='<?php echo esc_url($baseUrl);?>family#weddingdate' class='colored'>
                                <?php
                                $weddingDate = $family->getWeddingDate($user->ID);

                                if ($weddingDate) {
                                    echo esc_attr(gmdate('d F Y', strtotime($weddingDate)));
                                } else {
                                    ?>No weddingdate provided<?php
                                }
                                ?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }

                foreach ($children as $key => $child) {
                    $nr    = $key + 1;
                    ?>
                    <tr>
                        <td>
                            Child $nr:
                        </td>
                        <td>
                            <a href='<?php echo esc_url($baseUrl);?>family#children[$key]' class='colored'>
                                <?php echo esc_html(get_userdata($child)->display_name);?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }
        ?>
        <br>
        If any information is not correct, please correct it on <a href='<?php echo esc_url($accountPageUrl);?>'><?php echo str_replace(['https://www. ', 'https://'], '', $accountPageUrl)?></a>.
        <br>
        Or just click on any details listed above.<?php 

        wp_mail($user->user_email, $subject, ob_get_clean());
    }
}

/**
 * Notifies people that their account is about to expire or has been deleted
 */
function accountExpiryCheck()
{
    require_once(ABSPATH . 'wp-admin/includes/user.php');

    //Get the users who will expire in 1 month
    $users = get_users(
        array(
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'         => 'tsjippy_account_validity',
                    'compare'     => 'EXISTS'
                ),
                array(
                    'key'         => 'tsjippy_account_validity',
                    'value'       => 'unlimited',
                    'compare'     => '!='
                ),
                array(
                    'key'         => 'tsjippy_account_validity',
                    'value'       => gmdate("Y-m-d", strtotime(" +1 months")),
                    'compare'     => '=',
                    'type'        => 'DATE'
                ),

            ),
        )
    );

    foreach ($users as $user) {
        //Send e-mail
        $accountExpiryMail    = new AccountExpiryMail($user);
        $accountExpiryMail->filterMail();

        //Send the mail if valid email
        if (!str_contains($user->user_email, '.empty')) {
            $recipient = $user->user_email;
        } else {
            $recipient = '';
        }

        wp_mail($recipient, $accountExpiryMail->subject, $accountExpiryMail->message, $accountExpiryMail->headers);
    }

    //Get the users who are expired
    $expiredUsers = get_users(
        array(
            'meta_query'    => array(
                'relation'      => 'AND',
                array(
                    'key'       => 'tsjippy_account_validity',
                    'compare'   => 'EXISTS'
                ),
                array(
                    'key'       => 'tsjippy_account_validity',
                    'value'     => 'unlimited',
                    'compare'   => '!='
                ),
                array(
                    'key'       => 'tsjippy_account_validity',
                    'value'     => gmdate("Y-m-d"),
                    'compare'   => '<=',
                    'type'      => 'DATE'
                ),

            ),
        )
    );

    foreach ($expiredUsers as $user) {
        // check if it is a valid date string
        if (!strtotime(get_user_meta($user->ID, 'tsjippy_account_validity', true))) {
            continue;
        }

        //Delete the account
        TSJIPPY\printArray("Deleting user with id $user->ID and name $user->display_name as it was a temporary account. ");
        wp_delete_user($user->ID);
    }
}

/**
 * Send reminder to people to login
 */
function checkLastLoginDate()
{
    $users = TSJIPPY\getUserAccounts();
    foreach ($users as $user) {
        $lastLogin = get_user_meta($user->ID, 'tsjippy_last_login_date', true);

        //user has never logged in
        if (empty($lastLogin)) {
            //Send e-mail
            $to = $user->user_email;

            //Skip if not valid email
            if (str_contains($to, '.empty')) {
                continue;
            }

            $key         = get_password_reset_key($user);
            if (is_wp_error($key)) {
                return $key;
            }

            //TO DO
            $pageUrl     = get_permalink(TSJIPPY\LOGIN\SETTINGS['password-reset-page']);
            $url         = "$pageUrl?key=$key&login=$user->user_login";

            $mail = new AccountCreatedMail($user, $url);
            $mail->filterMail();

            wp_mail($to, $mail->subject, $mail->message);
        } else {
            $lastLoginDate       = date_create($lastLogin);
            $now                 = new \DateTime();
            $yearsSinceLastLogin = date_diff($lastLoginDate, $now)->format("%y");

            //User has not logged in in the last year
            if ($yearsSinceLastLogin > 0) {
                //Send e-mail
                $to = $user->user_email;
                //Skip if not valid email
                if (str_contains($to, '.empty')) {
                    continue;
                }

                //Send e-mail
                $weMissYouMail    = new WeMissYouMail($user, $lastLogin);
                $weMissYouMail->filterMail();

                wp_mail($to, $weMissYouMail->subject, $weMissYouMail->message);
            }
        }
    }
}
