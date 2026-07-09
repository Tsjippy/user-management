<?php

namespace TSJIPPY\USERMANAGEMENT;

use TSJIPPY;

class DashboardWarnings
{
    public int $reminderCount;
    public string $reminderHtml;
    public int $userId;

    public function __construct($userId)
    {
        $this->reminderCount    = 0;
        $this->reminderHtml     = '';
        $this->userId           = $userId;

        do_action('tsjippy-user-management-dashboard-warning-construct', $this);
    }
}
