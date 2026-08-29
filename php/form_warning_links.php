<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

add_filter('tsjippy-forms-reminder-link', function($link, $object, $element, $formUrl, $type, $childId){
        /**
         * Do nothing if not on account page
         */
        // phpcs:ignore
        if ( SETTINGS['account_page'] != $_SERVER['REQUEST_URI'] ?? '' ) {
            return $link;
        }

        /**
         * We are on the same page, just change the hash
         */
        // phpcs:ignore
        parse_str(wp_parse_url($formUrl, PHP_URL_QUERY), $params);
        $mainTab    = $params['main-tab'] ?? '';     
        $secondTab    = '';
        $names        = explode('[', $element->slug);
        if (count($names) > 1) {
            $secondTab    = $names[0];
        }

        return "<a onclick='Main.changeUrl(this, `$secondTab`)' data-target='$mainTab' data-hash={$element->slug} style='cursor:pointer'>$element->name</a>";
}, 10, 6);