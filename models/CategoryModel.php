<?php
/**
 * TaskFlow Pro — Category Model
 */
class CategoryModel
{
    public function getAll(int $userId): array
    {
        return Database::query(
            "SELECT c.*, (SELECT COUNT(*) FROM tasks t WHERE t.category_id = c.id AND t.user_id = c.user_id) AS task_count
             FROM categories c WHERE c.user_id = :uid ORDER BY c.sort_order ASC, c.name ASC",
            [':uid' => $userId]
        )->fetchAll();
    }

    public function create(int $userId, array $data): int
    {
        Database::query(
            "INSERT INTO categories (user_id, name, color, icon, sort_order) VALUES (:uid,:name,:color,:icon,:order)",
            [':uid' => $userId, ':name' => $data['name'], ':color' => $data['color'] ?? '#6C63FF',
             ':icon' => $data['icon'] ?? 'folder', ':order' => $data['sort_order'] ?? 0]
        );
        return (int) Database::lastInsertId();
    }

    public function delete(int $id, int $userId): bool
    {
        return Database::query(
            "DELETE FROM categories WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        )->rowCount() > 0;
    }
}