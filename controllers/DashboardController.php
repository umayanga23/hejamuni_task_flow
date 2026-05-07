<?php
/**
 * TaskFlow Pro — Dashboard Controller
 */
class DashboardController
{
    private AnalyticsModel $analytics;

    public function __construct() { $this->analytics = new AnalyticsModel(); }

    public function index(): void
    {
        requireAuth();
        require VIEW_PATH . '/pages/dashboard.php';
    }

    public function apiData(): void
    {
        requireAuth();
        jsonResponse(['success' => true, 'data' => $this->analytics->dashboardPayload(auth()['id'])]);
    }
}