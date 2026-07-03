<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

// phonenumbers and more
add_filter('tsjippy-forms-before-inserting-formdata', __NAMESPACE__ . '\beforeSavingData', 10, 2);
function beforeSavingData($request, $object)
{
    if ($object->formData->slug != 'user_generics') {
        return $request;
    }

    // check if childrens age is correct
    $children    = get_user_meta($object->userId, 'tsjippy_children');

    if (!empty($children)) {
        $ownAge        = strtotime(get_user_meta($object->userId, 'tsjippy_birthday', true));

        foreach ($children as $child) {
            $ageDiff    = strtotime(get_user_meta($child, 'tsjippy_birthday', true)) - $ownAge;

            if ($ageDiff / YEAR_IN_SECONDS < 14) {
                return new \WP_ERROR('forms', "Please don't lie");
            }
        }
    }

    //check if phonenumber has changed
    $oldPhonenumbers = get_user_meta($object->userId, 'tsjippy_phonenumbers');
    $newPhonenumbers = $request['phonenumbers'];
    $changedNumbers  = array_diff($newPhonenumbers, $oldPhonenumbers);
    foreach ($changedNumbers as $key => $changedNumber) {
        // Make sure the phonenumber is in the right format
        # = should be +
        if ($changedNumber[0] == '=') {
            $changedNumber = $request->phonenumbers[$key]    = str_replace('=', '+', $changedNumber);
        }

        # 00 should be +
        if (substr($changedNumber, 0, 2) == '00') {
            $changedNumber = $request->phonenumbers[$key]    = '+' . substr($changedNumber, 2);
        }

        # 0 should be +234
        if ($changedNumber[0] == '0') {
            $changedNumber = $request->phonenumbers[$key]    = '+234' . substr($changedNumber, 1);
        }

        # Should start with + by now
        if ($changedNumber[0] != '+') {
            $changedNumber = $request->phonenumbers[$key]    = '+234' . $changedNumber;
        }

        do_action('tsjippy-user-management-phonenumber-updated', $changedNumber, $object->userId);
    }

    // store changed date
    if (!empty($changedNumbers)) {
        update_user_meta($object->userId, 'tsjippy_phone-last-changed', time());
    }

    return $request;
}
