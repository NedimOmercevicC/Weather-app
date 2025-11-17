<?php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $userDao;

    public function __construct() {
        $this->userDao = new UserDao();
    }

    public function getAllUsers() {
        return $this->userDao->getAll();
    }

    public function getUserById($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        $user = $this->userDao->getById($id);
        if (!$user) {
            throw new NotFoundException("User not found");
        }
        return $user;
    }

    public function getUserByEmail($email) {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid email address");
        }
        return $this->userDao->getByEmail($email);
    }

    public function createUser($data) {
        $this->validateUserData($data, true);
        
        if ($this->userDao->emailExists($data['email'])) {
            throw new ValidationException("Email already exists");
        }

        // Support both 'password' and 'pass' keys
        if (isset($data['password'])) {
            $data['pass'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        } elseif (isset($data['pass'])) {
            $data['pass'] = password_hash($data['pass'], PASSWORD_DEFAULT);
        }

        if (!isset($data['is_admin'])) {
            $data['is_admin'] = false;
        }

        return $this->userDao->insert($data);
    }

    public function updateUser($id, $data) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid user ID");
        }

        $user = $this->userDao->getById($id);
        if (!$user) {
            throw new NotFoundException("User not found");
        }

        $this->validateUserData($data, false);

        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if ($this->userDao->emailExists($data['email'], $id)) {
                throw new ValidationException("Email already exists");
            }
        }

        // Support both 'password' and 'pass' keys
        if (isset($data['password'])) {
            $data['pass'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        } elseif (isset($data['pass'])) {
            $data['pass'] = password_hash($data['pass'], PASSWORD_DEFAULT);
        }

        return $this->userDao->update($id, $data);
    }

    public function deleteUser($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid user ID");
        }

        $user = $this->userDao->getById($id);
        if (!$user) {
            throw new NotFoundException("User not found");
        }

        return $this->userDao->delete($id);
    }

    public function authenticate($email, $password) {
        if (empty($email) || empty($password)) {
            throw new ValidationException("Email and password are required");
        }

        $user = $this->userDao->getByEmail($email);
        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['pass'])) {
            unset($user['pass']);
            return $user;
        }

        return false;
    }

    private function validateUserData($data, $isCreate) {
        if ($isCreate) {
            if (empty($data['fname'])) {
                throw new ValidationException("First name is required");
            }
            if (empty($data['lname'])) {
                throw new ValidationException("Last name is required");
            }
            if (empty($data['email'])) {
                throw new ValidationException("Email is required");
            }
            // Check for either 'password' or 'pass'
            if (empty($data['password']) && empty($data['pass'])) {
                throw new ValidationException("Password is required");
            }
        }

        if (isset($data['fname']) && strlen($data['fname']) > 50) {
            throw new ValidationException("First name must be 50 characters or less");
        }

        if (isset($data['lname']) && strlen($data['lname']) > 50) {
            throw new ValidationException("Last name must be 50 characters or less");
        }

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new ValidationException("Invalid email format");
            }
            if (strlen($data['email']) > 100) {
                throw new ValidationException("Email must be 100 characters or less");
            }
        }

        // Validate both 'password' and 'pass'
        $password = $data['password'] ?? $data['pass'] ?? '';
        if (isset($data['password']) || isset($data['pass'])) {
            if (strlen($password) < 6) {
                throw new ValidationException("Password must be at least 6 characters");
            }
        }
    }
}
?>

