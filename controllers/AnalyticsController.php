<?php
/**
 * TaskFlow Pro — Analytics Controller
 */
class AnalyticsController
{
    private AnalyticsModel $analytics;
    public function __construct() { $this->analytics = new AnalyticsModel(); }

    public function index(): void
    {
        requireAuth();
        require VIEW_PATH . '/pages/analytics.php';
    }

    public function apiData(): void
    {
        requireAuth();
        $type = $_GET['type'] ?? 'weekly';
        jsonResponse(['success' => true, 'data' => $this->analytics->buildReport(auth()['id'], $type)]);
    }
}