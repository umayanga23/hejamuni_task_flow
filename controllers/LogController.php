<?php
/**
 * TaskFlow Pro — Log Controller
 */
class LogController
{
    private DailyLogModel $logModel;
    public function __construct() { $this->logModel = new DailyLogModel(); }

    public function index(): void
    {
        requireAuth();
        $userId  = auth()['id'];
        $date    = $_GET['date'] ?? date('Y-m-d');
        $log     = $this->logModel->getOrCreate($userId, $date);
        $history = $this->logModel->getLast($userId, 14);
        require VIEW_PATH . '/pages/log.php';
    }

    public function save(): void
    {
        requireAuth();
        $date = $_POST['log_date'] ?? date('Y-m-d');
        $data = [
            'mood'                  => (int)($_POST['mood']          ?? 0) ?: null,
            'energy_level'          => (int)($_POST['energy_level']  ?? 0) ?: null,
            'focus_score'           => (int)($_POST['focus_score']   ?? 0) ?: null,
            'total_working_minutes' => (int)($_POST['total_working_minutes'] ?? 0),
            'productive_minutes'    => (int)($_POST['productive_minutes']    ?? 0),
            'break_minutes'         => (int)($_POST['break_minutes']         ?? 0),
            'tasks_planned'         => (int)($_POST['tasks_planned']         ?? 0),
            'tasks_completed'       => (int)($_POST['tasks_completed']       ?? 0),
            'reflection'            => trim($_POST['reflection'] ?? ''),
            'goals'                 => trim($_POST['goals']      ?? ''),
        ];
        $this->logModel->update(auth()['id'], $date, $data);
        jsonResponse(['success' => true, 'msg' => 'Log saved!']);
    }
}