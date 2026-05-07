<?php
/**
 * TaskFlow Pro — Report Controller
 */
class ReportController
{
    private AnalyticsModel $analytics;
    public function __construct() { $this->analytics = new AnalyticsModel(); }

    public function index(): void
    {
        requireAuth();
        $type   = $_GET['type'] ?? 'weekly';
        $report = $this->analytics->buildReport(auth()['id'], $type);
        require VIEW_PATH . '/pages/report.php';
    }

    public function export(): void
    {
        requireAuth();
        $type   = $_GET['type']   ?? 'weekly';
        $format = $_GET['format'] ?? 'csv';
        $data   = $this->analytics->buildReport(auth()['id'], $type);
        if ($format === 'csv') {
            $this->exportCsv($data, $type);
        } else {
            header('Content-Type: application/json');
            header("Content-Disposition: attachment; filename=taskflow-{$type}-report.json");
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        exit;
    }

    private function exportCsv(array $data, string $type): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=taskflow-{$type}-report.csv");
        $out = fopen('php://output', 'w');
        fputcsv($out, ['TaskFlow Pro — ' . ucfirst($type) . ' Report']);
        fputcsv($out, ['Period', $data['from'] . ' to ' . $data['to']]);
        fputcsv($out, ['Productivity Score', $data['score']]);
        fputcsv($out, ['Total Tasks', $data['summary']['total']]);
        fputcsv($out, ['Completed', $data['summary']['completed']]);
        fputcsv($out, ['Completion Rate %', $data['summary']['completion_rate']]);
        fputcsv($out, ['Avg Focus Score', $data['avgFocus']]);
        fputcsv($out, ['Avg Mood', $data['avgMood']]);
        fputcsv($out, ['Efficiency Rate %', $data['effRate']]);
        fputcsv($out, []);
        fputcsv($out, ['--- Daily Logs ---']);
        fputcsv($out, ['Date','Working Mins','Productive Mins','Break Mins','Tasks Done','Focus Score','Mood']);
        foreach ($data['logs'] as $log) {
            fputcsv($out, [$log['log_date'],$log['total_working_minutes'],$log['productive_minutes'],
                           $log['break_minutes'],$log['tasks_completed'],$log['focus_score'],$log['mood']]);
        }
        fclose($out);
    }
}