<?php
/**
 * TaskFlow Pro — Profile Controller
 */
class ProfileController
{
    private UserModel $userModel;
    public function __construct() { $this->userModel = new UserModel(); }

    public function index(): void
    {
        requireAuth();
        require VIEW_PATH . '/pages/profile.php';
    }

    public function update(): void
    {
        requireAuth();
        $data = [
            'name'     => trim($_POST['name']     ?? ''),
            'timezone' => trim($_POST['timezone'] ?? 'UTC'),
            'theme'    => in_array($_POST['theme'] ?? '', ['dark','light']) ? $_POST['theme'] : 'dark',
        ];
        $this->userModel->updateProfile(auth()['id'], $data);
        $_SESSION['user'] = $this->userModel->findById(auth()['id']);
        jsonResponse(['success' => true, 'msg' => 'Profile updated!']);
    }

    public function changePassword(): void
    {
        requireAuth();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        if (strlen($new) < 8) {
            jsonResponse(['success' => false, 'msg' => 'New password must be at least 8 characters.'], 422);
        }
        $ok = $this->userModel->changePassword(auth()['id'], $current, $new);
        jsonResponse($ok
            ? ['success' => true,  'msg' => 'Password changed!']
            : ['success' => false, 'msg' => 'Current password is incorrect.'],
            $ok ? 200 : 422
        );
    }
}