<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

/**
 * Displays the forms for children
 * 
 * @param   int $childId
 */
function showChildrenFields($childId)
{
    $availableForms        = SETTINGS['enabled-forms'] ?? [];

    ob_start();
    $active    = 'active';
    $hidden    = '';

    /**
     * Tabbuttons
     */
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

    /**
     * Tabcontents
     */
    if (isset($availableForms['generic'])) {
        ?>
        <div id='generic-child-info-<?php echo esc_attr($childId); ?>' class='tabcontent'>
            <?php
            $postId    = SETTINGS['child_generic'];
            $forms     = new TSJIPPY\FORMS\Forms( postId: $postId, userId: $childId);
            echo $forms->showForm();
            ?>
        </div>
        <?php

        $hidden    = 'hidden';
    }

    if (isset($availableForms['profile picture'])) {
        ?>
        <div id='profile-picture-child-info-<?php echo esc_attr($childId);?>' class='tabcontent <?php echo esc_attr($hidden);?>'>
            <?php 
            $postId    = SETTINGS['profile_picture'];
            $forms     = new TSJIPPY\FORMS\Forms( postId: $postId, userId: $childId);
            echo $forms->showForm();
            ?>
        </div>
        <?php
    }

    return ob_get_clean();
}
