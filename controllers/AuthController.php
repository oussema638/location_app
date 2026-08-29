<?php

class AuthController
{
    private Utilisateur $utilisateur;

    public function __construct()
    {
        $this->utilisateur = new Utilisateur();
    }

    public function login(): void
    {
        if (is_logged_in()) {
            redirect($this->homeForRole(current_user()['role']));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Jeton de sécurité invalide.');
                redirect('login');
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = $this->utilisateur->authenticate($email, $password);

            if ($user === null) {
                flash('error', 'Email ou mot de passe incorrect.');
                $_SESSION['old'] = ['email' => $email];
                redirect('login');
            }

            $_SESSION['user'] = $user;
            unset($_SESSION['old']);
            flash('success', 'Bienvenue ' . $user['prenom'] . '.');
            redirect($this->homeForRole($user['role']));
        }

        view('front/login');
    }

    public function register(): void
    {
        if (is_logged_in()) {
            redirect('');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Jeton de sécurité invalide.');
                redirect('register');
            }

            $data = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => 'client',
            ];
            $_SESSION['old'] = $data;

            $errors = $this->validate($data);
            if ($errors) {
                flash('error', implode(' ', $errors));
                redirect('register');
            }

            $id = $this->utilisateur->create($data);
            $user = $this->utilisateur->findById($id);
            $_SESSION['user'] = $user;
            unset($_SESSION['old']);
            flash('success', 'Compte créé. Vous pouvez réserver un équipement.');
            redirect('equipements');
        }

        view('front/register');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
        flash('success', 'Vous êtes déconnecté.');
        redirect('login');
    }

    public function utilisateurs(): void
    {
        require_responsable();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Jeton de sécurité invalide.');
                redirect('admin/utilisateurs');
            }

            $action = $_POST['action'] ?? '';
            $id = (int) ($_POST['id'] ?? 0);

            if ($action === 'update' && $id > 0) {
                $role = $_POST['role'] ?? 'client';
                if (!in_array($role, ROLES, true)) {
                    flash('error', 'Rôle invalide.');
                    redirect('admin/utilisateurs');
                }
                $user = $this->utilisateur->findById($id);
                if ($user) {
                    $this->utilisateur->update($id, [
                        'nom' => $user['nom'],
                        'prenom' => $user['prenom'],
                        'email' => $user['email'],
                        'role' => $role,
                    ]);
                    flash('success', 'Rôle mis à jour.');
                }
            }

            if ($action === 'delete' && $id > 0) {
                if ($id === (int) current_user()['id']) {
                    flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
                } else {
                    $this->utilisateur->delete($id);
                    flash('success', 'Utilisateur supprimé.');
                }
            }

            redirect('admin/utilisateurs');
        }

        view('back/utilisateurs', [
            'utilisateurs' => $this->utilisateur->findAll(),
        ]);
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['nom'] === '' || $data['prenom'] === '') {
            $errors[] = 'Nom et prénom sont obligatoires.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide.';
        } elseif ($this->utilisateur->emailExists($data['email'])) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
        if (strlen($data['password']) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        return $errors;
    }

    private function homeForRole(string $role): string
    {
        return in_array($role, ['agent', 'responsable'], true) ? 'admin' : '';
    }
}
