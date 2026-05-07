<?php
/**
 * TaskFlow Pro — Task Controller
 */
class TaskController
{
    private TaskModel     $taskModel;
    private CategoryModel $catModel;
    private TagModel      $tagModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->catModel  = new CategoryModel();
        $this->tagModel  = new TagModel();
    }

    public function index(): void
    {
        requireAuth();
        $userId     = auth()['id'];
        $filters    = array_filter([
            'status'      => $_GET['status']      ?? '',
            'priority'    => $_GET['priority']    ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'search'      => $_GET['search']      ?? '',
        ]);
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $tasks      = $this->taskModel->getAll($userId, $filters, $page);
        $categories = $this->catModel->getAll($userId);
        $tags       = $this->tagModel->getAll($userId);
        $summary    = $this->taskModel->getSummary($userId);
        require VIEW_PATH . '/pages/tasks.php';
    }

    public function board(): void
    {
        requireAuth();
        $userId      = auth()['id'];
        $pending     = $this->taskModel->getByStatus($userId, 'pending');
        $in_progress = $this->taskModel->getByStatus($userId, 'in_progress');
        $completed   = $this->taskModel->getByStatus($userId, 'completed');
        $delayed     = $this->taskModel->getByStatus($userId, 'delayed');
        $categories  = $this->catModel->getAll($userId);
        require VIEW_PATH . '/pages/board.php';
    }

    public function create(): void
    {
        requireAuth();
        $task       = null;
        $subtasks   = [];
        $categories = $this->catModel->getAll(auth()['id']);
        $tags       = $this->tagModel->getAll(auth()['id']);
        require VIEW_PATH . '/pages/task_form.php';
    }

    public function edit(): void
    {
        requireAuth();
        $id   = (int)($_GET['id'] ?? 0);
        $task = $this->taskModel->findById($id, auth()['id']);
        if (!$task) { header('Location: ' . APP_URL . '/tasks'); exit; }
        $subtasks   = $this->taskModel->getSubtasks($id, auth()['id']);
        $categories = $this->catModel->getAll(auth()['id']);
        $tags       = $this->tagModel->getAll(auth()['id']);
        require VIEW_PATH . '/pages/task_form.php';
    }

    public function store(): void
    {
        requireAuth();
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid CSRF token'];
            header('Location: ' . APP_URL . '/tasks/create'); exit;
        }
        $data   = $this->extractTaskData($_POST);
        $tagIds = array_map('intval', $_POST['tags'] ?? []);
        try {
            $this->taskModel->create($data, $tagIds);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Task created successfully!'];
            header('Location: ' . APP_URL . '/tasks');
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed: ' . $e->getMessage()];
            header('Location: ' . APP_URL . '/tasks/create');
        }
        exit;
    }

    public function update(): void
    {
        requireAuth();
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid CSRF token'];
            header('Location: ' . APP_URL . '/tasks'); exit;
        }
        $id     = (int)($_POST['id'] ?? 0);
        $data   = $this->extractTaskData($_POST);
        $tagIds = array_map('intval', $_POST['tags'] ?? []);
        try {
            $this->taskModel->update($id, auth()['id'], $data, $tagIds);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Task updated!'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => $e->getMessage()];
        }
        header('Location: ' . APP_URL . '/tasks'); exit;
    }

    public function delete(): void
    {
        requireAuth();
        $id = (int)($_POST['id'] ?? 0);
        $this->taskModel->delete($id, auth()['id']);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Task deleted.'];
        header('Location: ' . APP_URL . '/tasks'); exit;
    }

    public function updateStatus(): void
    {
        requireAuth();
        $id     = (int)($_POST['id']     ?? 0);
        $status = $_POST['status'] ?? '';
        $this->taskModel->updateStatus($id, auth()['id'], $status);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/tasks')); exit;
    }

    public function apiList(): void
    {
        requireAuth();
        $filters = array_filter([
            'status'      => $_GET['status']      ?? '',
            'priority'    => $_GET['priority']    ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'search'      => $_GET['search']      ?? '',
        ]);
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->taskModel->getAll(auth()['id'], $filters, $page);
        jsonResponse(['success' => true, 'data' => $result]);
    }

    public function apiCreate(): void
    {
        requireAuth();
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $body['csrf_token']
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? '';
        if (!verifyCsrf($token)) {
            jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
        }
        $data   = $this->extractTaskData($body);
        $tagIds = array_map('intval', $body['tags'] ?? []);
        try {
            $id   = $this->taskModel->create($data, $tagIds);
            $task = $this->taskModel->findById($id, auth()['id']);
            jsonResponse(['success' => true, 'task' => $task], 201);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function apiUpdate(): void
    {
        requireAuth();
        $id   = (int)($_GET['id'] ?? 0);
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        try {
            $this->taskModel->update($id, auth()['id'], $body, $body['tags'] ?? null);
            jsonResponse(['success' => true, 'task' => $this->taskModel->findById($id, auth()['id'])]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function apiDelete(): void
    {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        jsonResponse(['success' => $this->taskModel->delete($id, auth()['id'])]);
    }

    /**
     * Extract and sanitise task fields from any input array.
     * All keys are optional — missing ones default to null/empty safely.
     */
    private function extractTaskData(array $input): array
    {
        return [
            'user_id'           => auth()['id'],
            'title'             => trim($input['title']             ?? ''),
            'description'       => trim($input['description']       ?? ''),
            'priority'          => $input['priority']               ?? 'medium',
            'status'            => $input['status']                 ?? 'pending',
            // Use array_key_exists check via ?? to avoid undefined key warnings
            'category_id'       => isset($input['category_id'])    && $input['category_id']       !== '' ? (int)$input['category_id']       : null,
            'parent_id'         => isset($input['parent_id'])       && $input['parent_id']         !== '' ? (int)$input['parent_id']         : null,
            'due_date'          => isset($input['due_date'])        && $input['due_date']          !== '' ? $input['due_date']               : null,
            'start_time'        => isset($input['start_time'])      && $input['start_time']        !== '' ? $input['start_time']             : null,
            'end_time'          => isset($input['end_time'])        && $input['end_time']          !== '' ? $input['end_time']               : null,
            'estimated_minutes' => isset($input['estimated_minutes']) && $input['estimated_minutes'] !== '' ? (int)$input['estimated_minutes'] : null,
            'actual_minutes'    => isset($input['actual_minutes'])  && $input['actual_minutes']    !== '' ? (int)$input['actual_minutes']    : null,
            'notes'             => trim($input['notes']             ?? ''),
            'is_recurring'      => (int)($input['is_recurring']     ?? 0),
            'recurrence_type'   => isset($input['recurrence_type']) && $input['recurrence_type']   !== '' ? $input['recurrence_type']        : null,
        ];
    }
}