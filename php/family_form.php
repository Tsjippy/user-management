<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

//Add availbale partners as default
add_filter('tsjippy-add-form-multi-defaults', __NAMESPACE__ . '\addMultiDefault', 10, 3);
function addMultiDefault($defaultArrayValues, $userId, $formSlug)
{
    if ($formSlug != 'user_family') {
        return $defaultArrayValues;
    }

    $potentials    = new PotentialFamilyMembers($userId);

    $potentials->potentialParents();
    $defaultArrayValues['Potential fathers']     = $potentials->potentialFathers;
    $defaultArrayValues['Potential mothers']     = $potentials->potentialMothers;
    $defaultArrayValues['Potential spouses']    = $potentials->potentialSpouses();
    $defaultArrayValues['Potential children']    = $potentials->potentialChildren();

    return $defaultArrayValues;
}

//Save family picture
add_filter('tsjippy-before-inserting-formdata', __NAMESPACE__ . '\beforeSavingFormData', 10, 2);
function beforeSavingFormData($submission, $object)
{
    if ($object->formData->slug != 'user_family') {
        return $submission;
    }

    $userId    = $object->userId;

    $family = new TSJIPPY\FAMILY\Family();

    // Family Picture
    $newPicture    = TSJIPPY\sanitize($_POST['family_picture']);
    $oldPicture    = $family->getFamilyMeta($userId, 'family_picture', true);
    if ($newPicture != $oldPicture) {
        // Do not show in picture gallery
        update_post_meta($newPicture, 'tsjippy_gallery_visibility', 'hide');

        do_action('tsjippy-update-family-picture', $userId, $newPicture);
    }

    return $submission;
}

// add a family member modal
add_filter('tsjippy-before-form', __NAMESPACE__ . '\beforeForm', 10, 2);
function beforeForm($html, $formSlug)
{
    if ($formSlug != 'user_family') {
        return $html;
    }

    if (isset($_GET['user-id'])) {
        $lastname = get_userdata((int) $_GET['user-id'])->last_name;
    } else {
        $lastname = wp_get_current_user()->last_name;
    }

    ob_start();

?>
    <div id='add-account-modal' class="modal hidden">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form action="" method="post" id="add-member-form">
                <p>Please fill in the form to create a user profile for a family member</p>

                <label>
                    <h4>First name</h4>
                    <input type="text" class='wide' name="first-name">
                </label>

                <label>
                    <h4>Last name</h4>
                    <input type="text" name="last-name" class='wide' value="<?php echo esc_attr($lastname); ?>">
                </label>

                <label>
                    <h4>E-mail</h4>
                    <input type="email" class='wide' name="email">
                </label>

                <?php TSJIPPY\addSaveButton('adduseraccount', 'Add family member'); ?>
            </form>
        </div>
    </div>
<?php

    return $html . ob_get_clean();
}
