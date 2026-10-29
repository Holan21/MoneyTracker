<?php

declare(strict_types=1);

class AuthValidator extends Validator
{
    /** @return array{login:string,password:string} */
    public function login(array $data): array
    {
        $this->errors = [];

        $login    = trim((string)($data['login'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if (!ValidationService::required($login)) {
            $this->addError('login', 'Login is required');
        }
        if (!ValidationService::required($password)) {
            $this->addError('password', 'Password is required');
        }
        if ($this->errors !== []) {
            throw new ValidationException('Login and password are required', $this->errors);
        }

        return ['login' => $login, 'password' => $password];
    }

    /** @return array{name:string,login:string,password:string} */
    public function register(array $data): array
    {
        $this->errors = [];

        $name     = trim((string)($data['name'] ?? ''));
        $login    = trim((string)($data['login'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $confirm  = (string)($data['password_confirm'] ?? '');

        if (!ValidationService::required($name)) {
            $this->addError('name', 'Name is required');
        } elseif (!ValidationService::minLength($name, 2)) {
            $this->addError('name', 'Name is too short');
        } elseif (!ValidationService::maxLength($name, 100)) {
            $this->addError('name', 'Name is too long');
        }

        if (!ValidationService::required($login)) {
            $this->addError('login', 'Login is required');
        } elseif (!ValidationService::loginFormat($login)) {
            $this->addError('login', 'Login must be 3-32 letters, numbers or ._-');
        }

        if (!ValidationService::minLength($password, 4)) {
            $this->addError('password', 'Password must be at least 4 characters');
        }
        if ($password !== $confirm) {
            $this->addError('password_confirm', 'Passwords do not match');
        }

        $this->failIfErrors();

        return [
            'name'     => $name,
            'login'    => $login,
            'password' => $password,
        ];
    }
}
