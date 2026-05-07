<?php
/**
 * TaskFlow Pro — Tag Model
 */
class TagModel
{
    public function getAll(int $userId): array
    {
        return Database::query(
            "SELECT * FROM tags WHERE user_id = :uid ORDER BY name",
            [':uid' => $userId]
        )->fetchAll();
    }

    public function create(int $userId, string $name, string $color = '#888888'): int
    {
        Database::query(
            "INSERT IGNORE INTO tags (user_id, name, color) VALUES (?,?,?)",
            [$userId, $name, $color]
        );
        $row = Database::query(
            "SELECT id FROM tags WHERE user_id = ? AND name = ?",
            [$userId, $name]
        )->fetch();
        return (int) ($row['id'] ?? 0);
    }

    public function delete(int $id, int $userId): bool
    {
        return Database::query(
            "DELETE FROM tags WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        )->rowCount() > 0;
    }
}