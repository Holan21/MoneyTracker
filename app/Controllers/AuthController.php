<?php

declare(strict_types=1);

class AuthController extends Controller
{
    public function loginForm(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->renderLogin();
    }

    public function login(array $params = []): void
    {
        try {
            $user = (new AuthService())->authenticate($_POST);
            Auth::login($user);
            $this->redirect('/');
        } catch (PDOException) {
            $this->renderLogin('Database is unavailable. Start MySQL and try again.', $_POST);
        } catch (ValidationException $e) {
            $this->renderLogin($e->getMessage(), $_POST, $e->getErrors());
        }
    }

    public function registerForm(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->renderRegister();
    }

    public function register(array $params = []): void
    {
        try {
            $user = (new AuthService())->register($_POST);
            Auth::login($user);
            $this->redirect('/');
        } catch (PDOException) {
            $this->renderRegister('Database is unavailable. Start MySQL and try again.', $_POST);
        } catch (ValidationException $e) {
            $this->renderRegister($e->getMessage(), $_POST, $e->getErrors());
        }
    }

    private function renderLogin(?string $error = null, array $old = [], array $errors = []): void
    {
        $this->view('auth/login', [
            'title'  => 'Login',
            'layout' => 'auth',
            'error'  => $error,
            'errors' => $errors,
            'form'   => [
                'login' => (string)($old['login'] ?? ''),
            ],
        ]);
    }

    private function renderRegister(?string $error = null, array $old = [], array $errors = []): void
    {
        $this->view('auth/register', [
            'title'  => 'Register',
            'layout' => 'auth',
            'error'  => $error,
            'errors' => $errors,
            'form'   => [
                'name'  => (string)($old['name'] ?? ''),
                'login' => (string)($old['login'] ?? ''),
            ],
        ]);
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
