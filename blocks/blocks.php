<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', __NAMESPACE__ . '\initBlocks');
function initBlocks()
{
    register_block_type(
        'tsjippy-user-management/pending-user-accounts',
        array(
            'title'           => __( 'Pending User Accounts', 'tsjippy' ),
            'render_callback' => __NAMESPACE__.'\pendingUsers',
            'supports'        => array(
                'autoRegister' => true,
            ),
            'icon'  => 'users'
        )
    );

    register_block_type(
        'tsjippy-user-management/user-statistics',
        array(
            'title'           => __( 'User Statistics', 'tsjippy' ),
            'render_callback' => __NAMESPACE__.'\userStatistics',
            'supports'        => array(
                'autoRegister' => true,
            ),
            'icon'  => 'users'
        )
    );

    register_block_type(
        'tsjippy-user-management/request-user-accounts',
        array(
            'title'           => __( 'Request User Account Form', 'tsjippy' ),
            'render_callback' => __NAMESPACE__.'\requestAccount',
            'supports'        => array(
                'autoRegister' => true,
            ),
            'icon'  => 'users'
        )
    );
}