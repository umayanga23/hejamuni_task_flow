<?php
/**
 * TaskFlow Pro — User Model
 */
class UserModel
{
    public function findById(int $id): ?array
    {
        return Database::query(
            "SELECT id, name, email, avatar, timezone, theme, created_at, last_login
             FROM users WHERE id = :id AND is_active = 1",
            [':id' => $id]
        )->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        return Database::query(
            "SELECT * FROM users WHERE email = :email AND is_active = 1",
            [':email' => $email]
        )->fetch() ?: null;
    }

    public function create(array $data): int
    {
        Database::query(
            "INSERT INTO users (name, email, password_hash, timezone)
             VALUES (:name, :email, :password_hash, :timezone)",
            [
                ':name'          => $data['name'],
                ':email'         => $data['email'],
                ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                ':timezone'      => $data['timezone'] ?? 'UTC',
            ]
        );
        return (int) Database::lastInsertId();
    }

    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if (!password_verify($password, $user['password_hash'])) return null;
        Database::query("UPDATE users SET last_login = NOW() WHERE id = :id", [':id' => $user['id']]);
        unset($user['password_hash']);
        return $user;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $allowed = ['name', 'timezone', 'theme'];
        $sets    = [];
        $params  = [':id' => $id];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $sets[]       = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($sets)) return false;
        Database::query("UPDATE users SET " . implode(',', $sets) . " WHERE id = :id", $params);
        return true;
    }

    public function changePassword(int $id, string $current, string $new): bool
    {
        $row = Database::query(
            "SELECT password_hash FROM users WHERE id = :id", [':id' => $id]
        )->fetch();
        if (!$row || !password_verify($current, $row['password_hash'])) return false;
        Database::query(
            "UPDATE users SET password_hash = :hash WHERE id = :id",
            [':hash' => password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]), ':id' => $id]
        );
        return true;
    }
}