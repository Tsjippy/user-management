<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

/**
 * Displays the forms for children
 */
function showChildrenFields($childId)
{
    $availableForms        = SETTINGS['enabled-forms'] ?? [];

    ob_start();
    $active    = 'active';
    $hidden    = '';
    if (isset($availableForms['generic'])) {
        ?>
        <button class=' button tablink active' id='show-generic-child-info-<?php echo esc_attr($childId);?>' data-target='generic-child-info-<?php echo esc_attr($childId);?>'>
            Generic info
        </button>
        <?php
        $active = '';
    }

    if (isset($availableForms['profile picture'])) {
        ?>
        <button class='button tablink <?php echo esc_attr($active);?>' id='show-profile-picture-child-info-<?php echo esc_attr($childId);?>' data-target='profile-picture-child-info-<?php echo esc_attr($childId);?>'>
            Profile picture
        </button>
        <?php
    }

    if (isset($availableForms['generic'])) {
        ?>
        <div id='generic-child-info-<?php echo esc_attr($childId); ?>' class='tabcontent'>
            <?php 
            $forms  = new TSJIPPY\FORMS\DisplayForm( [
                'slug'    => 'child_generic',
                'user-id' => $childId
            ] );
            $forms->showForm(true);
            ?>
        </div>
        <?php

        $hidden    = 'hidden';
    }

    if (isset($availableForms['profile picture'])) {
        ?>
        <div id='profile-picture-child-info-<?php echo esc_attr($childId);?>' class='tabcontent <?php echo esc_attr($hidden);?>'>
            <?php 
            $forms  = new TSJIPPY\FORMS\DisplayForm( [
                'slug'    => 'profile_picture',
                'user-id' => $childId
            ] );
            $forms->showForm(true);
            ?>
        </div>
        <?php
    }

    return ob_get_clean();
}
