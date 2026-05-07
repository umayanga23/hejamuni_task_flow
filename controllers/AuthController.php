<?php
/**
 * TaskFlow Pro — Auth Controller
 */
class AuthController
{
    private UserModel $userModel;

    public function __construct() { $this->userModel = new UserModel(); }

    public function showLogin(): void
    {
        if (auth()) { header('Location: ' . APP_URL); exit; }
        require VIEW_PATH . '/pages/login.php';
    }

    public function login(): void
    {
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid CSRF token'];
            header('Location: ' . APP_URL . '/login'); exit;
        }
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        if (!$email || !$password) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Email and password are required.'];
            header('Location: ' . APP_URL . '/login'); exit;
        }
        $user = $this->userModel->verifyPassword($email, $password);
        if (!$user) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid credentials. Please try again.'];
            header('Location: ' . APP_URL . '/login'); exit;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        header('Location: ' . APP_URL . '/dashboard'); exit;
    }

    public function showRegister(): void
    {
        if (auth()) { header('Location: ' . APP_URL); exit; }
        require VIEW_PATH . '/pages/register.php';
    }

    public function register(): void
    {
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid CSRF token'];
            header('Location: ' . APP_URL . '/register'); exit;
        }
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $errors   = [];
        if (strlen($name) < 2)                         $errors[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (strlen($password) < 8)                     $errors[] = 'Password must be at least 8 characters.';
        if ($this->userModel->findByEmail($email))     $errors[] = 'Email already registered.';
        if ($errors) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => implode(' ', $errors)];
            header('Location: ' . APP_URL . '/register'); exit;
        }
        $userId = $this->userModel->create(compact('name','email','password'));
        $user   = $this->userModel->findById($userId);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        // Seed default categories
        foreach ([['Work','#6C63FF','briefcase'],['Personal','#FF6584','user'],['Study','#43C6AC','book'],['Health','#F7971E','heart']] as $i => [$n,$c,$ic]) {
            Database::query("INSERT INTO categories (user_id,name,color,icon,sort_order) VALUES (?,?,?,?,?)", [$userId,$n,$c,$ic,$i+1]);
        }
        header('Location: ' . APP_URL . '/dashboard'); exit;
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ' . APP_URL . '/login'); exit;
    }
}