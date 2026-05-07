<?php
/**
 * TaskFlow Pro — Analytics Model
 */
class AnalyticsModel
{
    private TaskModel     $taskModel;
    private DailyLogModel $logModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->logModel  = new DailyLogModel();
    }

    public function dashboardPayload(int $userId): array
    {
        $summary      = $this->taskModel->getSummary($userId);
        $score        = $this->taskModel->productivityScore($userId);
        $trend        = $this->taskModel->completionTrend($userId, 14);
        $byCat        = $this->taskModel->timeByCategory($userId);
        $weekly       = $this->taskModel->weeklyStats($userId);
        $priority     = $this->taskModel->priorityDistribution($userId);
        $insights     = $this->taskModel->workloadInsights($userId);
        $today        = $this->logModel->getOrCreate($userId, date('Y-m-d'));
        $recentLogs   = $this->logModel->getLast($userId, 7);

        $efficiencyRate = $today['total_working_minutes'] > 0
            ? round($today['productive_minutes'] / $today['total_working_minutes'] * 100, 1)
            : 0;

        return compact('summary','score','trend','byCat','weekly','priority','insights','today','efficiencyRate','recentLogs');
    }

    public function buildReport(int $userId, string $type): array
    {
        [$from, $to] = match($type) {
            'weekly'  => [date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
            'monthly' => [date('Y-m-01'), date('Y-m-d')],
            default   => [date('Y-m-d'), date('Y-m-d')],
        };

        $logs      = $this->logModel->getRange($userId, $from, $to);
        $days      = max(1, (int)((strtotime($to) - strtotime($from)) / 86400) + 1);
        $trend     = $this->taskModel->completionTrend($userId, $days);
        $totalWork = array_sum(array_column($logs, 'total_working_minutes'));
        $totalProd = array_sum(array_column($logs, 'productive_minutes'));
        $avgFocus  = count($logs) ? round(array_sum(array_column($logs, 'focus_score')) / count($logs), 1) : 0;
        $avgMood   = count($logs) ? round(array_sum(array_column($logs, 'mood')) / count($logs), 1) : 0;

        return [
            'type'      => $type,
            'from'      => $from,
            'to'        => $to,
            'summary'   => $this->taskModel->getSummary($userId),
            'score'     => $this->taskModel->productivityScore($userId),
            'trend'     => $trend,
            'byCat'     => $this->taskModel->timeByCategory($userId),
            'logs'      => $logs,
            'totalWork' => $totalWork,
            'totalProd' => $totalProd,
            'avgFocus'  => $avgFocus,
            'avgMood'   => $avgMood,
            'effRate'   => $totalWork > 0 ? round($totalProd / $totalWork * 100, 1) : 0,
        ];
    }
}