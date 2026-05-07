<?php
/**
 * TaskFlow Pro — DailyLog Model
 */
class DailyLogModel
{
    public function getOrCreate(int $userId, string $date): array
    {
        $row = Database::query(
            "SELECT * FROM daily_logs WHERE user_id = :uid AND log_date = :d",
            [':uid' => $userId, ':d' => $date]
        )->fetch();

        if (!$row) {
            Database::query(
                "INSERT INTO daily_logs (user_id, log_date) VALUES (:uid, :d)",
                [':uid' => $userId, ':d' => $date]
            );
            $row = [
                'id' => (int)Database::lastInsertId(), 'user_id' => $userId, 'log_date' => $date,
                'mood' => null, 'energy_level' => null, 'focus_score' => null,
                'total_working_minutes' => 0, 'productive_minutes' => 0, 'break_minutes' => 0,
                'tasks_planned' => 0, 'tasks_completed' => 0, 'reflection' => null, 'goals' => null,
            ];
        }
        return $row;
    }

    public function update(int $userId, string $date, array $data): bool
    {
        $allowed = ['mood','energy_level','focus_score','total_working_minutes',
                    'productive_minutes','break_minutes','tasks_planned','tasks_completed',
                    'reflection','goals'];
        $sets   = [];
        $params = [':uid' => $userId, ':d' => $date];

        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $sets[]        = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($sets)) return false;

        Database::query(
            "INSERT INTO daily_logs (user_id, log_date) VALUES (:uid, :d)
             ON DUPLICATE KEY UPDATE user_id = user_id",
            [':uid' => $userId, ':d' => $date]
        );
        Database::query(
            "UPDATE daily_logs SET " . implode(',', $sets) . " WHERE user_id = :uid AND log_date = :d",
            $params
        );
        return true;
    }

    public function getRange(int $userId, string $from, string $to): array
    {
        return Database::query(
            "SELECT * FROM daily_logs
             WHERE user_id = :uid AND log_date BETWEEN :from AND :to
             ORDER BY log_date ASC",
            [':uid' => $userId, ':from' => $from, ':to' => $to]
        )->fetchAll();
    }

    public function getLast(int $userId, int $days = 30): array
    {
        return Database::query(
            "SELECT * FROM daily_logs
             WHERE user_id = :uid AND log_date >= DATE_SUB(CURDATE(), INTERVAL :d DAY)
             ORDER BY log_date DESC",
            [':uid' => $userId, ':d' => $days]
        )->fetchAll();
    }
}