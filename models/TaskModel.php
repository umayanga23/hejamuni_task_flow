<?php
/**
 * TaskFlow Pro — Task Model
 */
class TaskModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ---- CREATE ----

    public function create(array $data, array $tagIds = []): int
    {
        Database::beginTransaction();
        try {
            $sql = "
                INSERT INTO tasks
                    (user_id, category_id, parent_id, title, description, priority,
                     status, start_time, end_time, due_date, estimated_minutes,
                     is_recurring, recurrence_type, recurrence_end, notes, position)
                VALUES
                    (:user_id, :category_id, :parent_id, :title, :description, :priority,
                     :status, :start_time, :end_time, :due_date, :estimated_minutes,
                     :is_recurring, :recurrence_type, :recurrence_end, :notes,
                     (SELECT COALESCE(MAX(position),0)+1 FROM tasks t2 WHERE t2.user_id = :uid2))
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id'           => $data['user_id'],
                ':uid2'              => $data['user_id'],
                ':category_id'       => $data['category_id'] ?? null,
                ':parent_id'         => $data['parent_id'] ?? null,
                ':title'             => $data['title'],
                ':description'       => $data['description'] ?? null,
                ':priority'          => $data['priority'] ?? 'medium',
                ':status'            => $data['status'] ?? 'pending',
                ':start_time'        => $data['start_time'] ?? null,
                ':end_time'          => $data['end_time'] ?? null,
                ':due_date'          => $data['due_date'] ?? null,
                ':estimated_minutes' => $data['estimated_minutes'] ?? null,
                ':is_recurring'      => $data['is_recurring'] ?? 0,
                ':recurrence_type'   => $data['recurrence_type'] ?? null,
                ':recurrence_end'    => $data['recurrence_end'] ?? null,
                ':notes'             => $data['notes'] ?? null,
            ]);
            $taskId = (int) Database::lastInsertId();
            if (!empty($tagIds)) $this->syncTags($taskId, $tagIds);
            Database::commit();
            return $taskId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    // ---- READ ----

    public function findById(int $id, int $userId): ?array
    {
        $sql = "
            SELECT t.*, c.name AS category_name, c.color AS category_color,
                   (SELECT COUNT(*) FROM tasks st WHERE st.parent_id = t.id) AS subtask_count,
                   (SELECT COUNT(*) FROM tasks st WHERE st.parent_id = t.id AND st.status = 'completed') AS subtask_done
            FROM   tasks t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE  t.id = :id AND t.user_id = :user_id
        ";
        $row = Database::query($sql, [':id' => $id, ':user_id' => $userId])->fetch();
        if (!$row) return null;
        $row['tags'] = $this->getTaskTags($id);
        return $row;
    }

    public function getAll(int $userId, array $filters = [], int $page = 1, int $perPage = TASKS_PER_PAGE): array
    {
        $where  = ['t.user_id = :user_id', 't.parent_id IS NULL'];
        $params = [':user_id' => $userId];

        if (!empty($filters['status']))      { $where[] = 't.status = :status';         $params[':status']      = $filters['status']; }
        if (!empty($filters['priority']))    { $where[] = 't.priority = :priority';     $params[':priority']    = $filters['priority']; }
        if (!empty($filters['category_id'])) { $where[] = 't.category_id = :cat';      $params[':cat']         = $filters['category_id']; }
        if (!empty($filters['search']))      { $where[] = '(t.title LIKE :search OR t.description LIKE :search)'; $params[':search'] = '%'.$filters['search'].'%'; }
        if (!empty($filters['due_before']))  { $where[] = 't.due_date <= :due_before';  $params[':due_before']  = $filters['due_before']; }

        $whereSQL = implode(' AND ', $where);
        $total    = (int) Database::query("SELECT COUNT(*) FROM tasks t WHERE $whereSQL", $params)->fetchColumn();
        $offset   = ($page - 1) * $perPage;

        $sql = "
            SELECT t.*, c.name AS category_name, c.color AS category_color,
                   (SELECT COUNT(*) FROM tasks st WHERE st.parent_id = t.id) AS subtask_count,
                   CASE WHEN t.due_date < CURDATE() AND t.status NOT IN ('completed','cancelled') THEN 1 ELSE 0 END AS is_overdue
            FROM   tasks t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE  $whereSQL
            ORDER  BY FIELD(t.priority,'critical','high','medium','low'), t.due_date IS NULL, t.due_date ASC, t.created_at DESC
            LIMIT  :lim OFFSET :off
        ";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return ['data' => $stmt->fetchAll(), 'total' => $total, 'pages' => (int)ceil($total/$perPage), 'page' => $page];
    }

    public function getSubtasks(int $parentId, int $userId): array
    {
        return Database::query(
            "SELECT * FROM tasks WHERE parent_id = :pid AND user_id = :uid ORDER BY position ASC",
            [':pid' => $parentId, ':uid' => $userId]
        )->fetchAll();
    }

    // public function getByStatus(int $userId, string $status): array
    // {
    //     return Database::query(
    //         "SELECT t.*, c.name AS category_name, c.color AS category_color,
    //                 CASE WHEN t.due_date < CURDATE() AND t.status NOT IN ('completed','cancelled') THEN 1 ELSE 0 END AS is_overdue
    //          FROM tasks t LEFT JOIN categories c ON c.id = t.category_id
    //          WHERE t.user_id = :uid AND t.status = :status AND t.parent_id IS NULL
    //          ORDER BY FIELD(t.priority,'critical','high','medium','low'), t.due_date IS NULL, t.due_date ASC",
    //         [':uid' => $userId, ':status' => $status]
    //     )->fetchAll();
    // }

    public function getByStatus(int $userId, string $status): array
{
    return Database::query("
        SELECT t.*, c.name AS category_name, c.color AS category_color,

               (SELECT COUNT(*) 
                FROM tasks st 
                WHERE st.parent_id = t.id) AS subtask_count,

               (SELECT COUNT(*) 
                FROM tasks st 
                WHERE st.parent_id = t.id AND st.status = 'completed') AS subtask_done,

               CASE 
                 WHEN t.due_date < CURDATE() 
                  AND t.status NOT IN ('completed','cancelled') 
                 THEN 1 ELSE 0 
               END AS is_overdue

        FROM tasks t 
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.user_id = :uid 
          AND t.status = :status 
          AND t.parent_id IS NULL
        ORDER BY FIELD(t.priority,'critical','high','medium','low'),
                 t.due_date IS NULL,
                 t.due_date ASC
    ", [
        ':uid' => $userId,
        ':status' => $status
    ])->fetchAll();
}

    // ---- UPDATE ----

    public function update(int $id, int $userId, array $data, ?array $tagIds = null): bool
    {
        Database::beginTransaction();
        try {
            $allowed = ['title','description','priority','status','category_id','parent_id',
                        'start_time','end_time','due_date','estimated_minutes','actual_minutes',
                        'completion_pct','is_recurring','recurrence_type','recurrence_end','notes'];
            $sets   = [];
            $params = [':id' => $id, ':user_id' => $userId];

            foreach ($allowed as $field) {
                if (array_key_exists($field, $data)) {
                    $sets[]          = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            if (isset($data['status'])) {
                if ($data['status'] === 'completed') {
                    $sets[] = 'completed_at = NOW()';
                    $sets[] = 'completion_pct = 100';
                } elseif (in_array($data['status'], ['pending','in_progress'])) {
                    $sets[] = 'completed_at = NULL';
                }
            }
            if (empty($sets)) { Database::rollBack(); return false; }

            Database::query(
                "UPDATE tasks SET " . implode(', ', $sets) . " WHERE id = :id AND user_id = :user_id",
                $params
            );
            if ($tagIds !== null) $this->syncTags($id, $tagIds);
            Database::commit();
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, int $userId, string $status): bool
    {
        return $this->update($id, $userId, ['status' => $status]);
    }

    public function updatePosition(int $id, int $userId, int $position): bool
    {
        Database::query(
            "UPDATE tasks SET position = :pos WHERE id = :id AND user_id = :uid",
            [':pos' => $position, ':id' => $id, ':uid' => $userId]
        );
        return true;
    }

    // ---- DELETE ----

    public function delete(int $id, int $userId): bool
    {
        $stmt = Database::query(
            "DELETE FROM tasks WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        );
        return $stmt->rowCount() > 0;
    }

    // ---- TAGS ----

    private function syncTags(int $taskId, array $tagIds): void
    {
        Database::query("DELETE FROM task_tags WHERE task_id = :tid", [':tid' => $taskId]);
        if (empty($tagIds)) return;
        $placeholders = implode(',', array_fill(0, count($tagIds), '(?,?)'));
        $values = [];
        foreach ($tagIds as $tid) { $values[] = $taskId; $values[] = (int)$tid; }
        $this->db->prepare("INSERT INTO task_tags (task_id, tag_id) VALUES $placeholders")->execute($values);
    }

    public function getTaskTags(int $taskId): array
    {
        return Database::query(
            "SELECT tg.* FROM tags tg JOIN task_tags tt ON tt.tag_id = tg.id WHERE tt.task_id = :tid",
            [':tid' => $taskId]
        )->fetchAll();
    }

    // ---- ANALYTICS ----

    public function getSummary(int $userId): array
    {
        return Database::query("
            SELECT
                COUNT(*) AS total,
                SUM(status='completed') AS completed,
                SUM(status='pending') AS pending,
                SUM(status='in_progress') AS in_progress,
                SUM(status='delayed') AS `delayed`,
                SUM(due_date < CURDATE() AND status NOT IN ('completed','cancelled')) AS overdue,
                ROUND(AVG(CASE WHEN actual_minutes > 0 THEN actual_minutes END)) AS avg_actual_min,
                ROUND(AVG(CASE WHEN estimated_minutes > 0 THEN estimated_minutes END)) AS avg_est_min,
                ROUND(100.0 * SUM(status='completed') / NULLIF(COUNT(*),0), 1) AS completion_rate
            FROM tasks WHERE user_id = :uid AND parent_id IS NULL
        ", [':uid' => $userId])->fetch() ?: [];
    }

    public function completionTrend(int $userId, int $days = 30): array
    {
        return Database::query("
            SELECT DATE(completed_at) AS day, COUNT(*) AS count
            FROM tasks
            WHERE user_id = :uid AND status = 'completed'
              AND completed_at >= DATE_SUB(CURDATE(), INTERVAL :d DAY)
            GROUP BY day ORDER BY day ASC
        ", [':uid' => $userId, ':d' => $days])->fetchAll();
    }

    public function timeByCategory(int $userId): array
    {
        return Database::query("
            SELECT c.name, c.color, SUM(t.actual_minutes) AS total_minutes
            FROM tasks t JOIN categories c ON c.id = t.category_id
            WHERE t.user_id = :uid AND t.actual_minutes IS NOT NULL
            GROUP BY c.id ORDER BY total_minutes DESC
        ", [':uid' => $userId])->fetchAll();
    }

    public function priorityDistribution(int $userId): array
    {
        return Database::query("
            SELECT priority, COUNT(*) AS count
            FROM tasks WHERE user_id = :uid AND parent_id IS NULL GROUP BY priority
        ", [':uid' => $userId])->fetchAll();
    }

    public function weeklyStats(int $userId): array
    {
        return Database::query("
            SELECT DAYNAME(completed_at) AS day_name, DAYOFWEEK(completed_at) AS day_num,
                   COUNT(*) AS completed, SUM(actual_minutes) AS minutes_worked
            FROM tasks
            WHERE user_id = :uid AND status = 'completed'
              AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY day_name, day_num ORDER BY day_num
        ", [':uid' => $userId])->fetchAll();
    }

    public function productivityScore(int $userId): float
    {
        $summary = $this->getSummary($userId);
        $completionRate = (float)($summary['completion_rate'] ?? 0);

        $timeAcc = 0;
        if (($summary['avg_est_min'] ?? 0) > 0 && ($summary['avg_actual_min'] ?? 0) > 0) {
            $diff    = abs($summary['avg_actual_min'] - $summary['avg_est_min']);
            $timeAcc = max(0, 1 - $diff / $summary['avg_est_min']) * 100;
        }

        $focusRow = Database::query("
            SELECT AVG(focus_score) AS avg_focus FROM daily_logs
            WHERE user_id = :uid AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ", [':uid' => $userId])->fetch();
        $focusAvg = (float)($focusRow['avg_focus'] ?? 70);

        $score = ($completionRate * PRODUCTIVITY_WEIGHT_COMPLETION)
               + ($timeAcc        * PRODUCTIVITY_WEIGHT_TIME_ACC)
               + ($focusAvg       * PRODUCTIVITY_WEIGHT_FOCUS);
        return round(min(100, $score), 1);
    }

    public function workloadInsights(int $userId): array
    {
        $logs = Database::query("
            SELECT AVG(total_working_minutes) AS avg_work,
                   AVG(productive_minutes) AS avg_prod,
                   MAX(total_working_minutes) AS max_work
            FROM daily_logs
            WHERE user_id = :uid AND log_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
        ", [':uid' => $userId])->fetch();

        $avgWork  = (float)($logs['avg_work'] ?? 0);
        $insights = [];

        if ($avgWork > 600) {
            $insights[] = ['type' => 'warning', 'msg' => 'Overworking detected: averaging ' . round($avgWork/60,1) . ' hrs/day'];
        } elseif ($avgWork < 240 && $avgWork > 0) {
            $insights[] = ['type' => 'info', 'msg' => 'Underutilisation detected: averaging only ' . round($avgWork/60,1) . ' hrs/day'];
        } else {
            $insights[] = ['type' => 'success', 'msg' => 'Workload balance looks healthy'];
        }

        $effRate = $avgWork > 0 ? round(((float)($logs['avg_prod'] ?? 0)) / $avgWork * 100, 1) : 0;
        $insights[] = ['type' => 'metric', 'msg' => "Efficiency rate: {$effRate}%"];
        return ['insights' => $insights, 'logs' => $logs];
    }
}