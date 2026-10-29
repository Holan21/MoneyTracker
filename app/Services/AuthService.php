<?php

declare(strict_types=1);

class AuthService
{
    private ?UserRepository $repo = null;

    private function repo(): UserRepository
    {
        return $this->repo ??= new UserRepository();
    }

    public function findByLogin(string $login): array|false
    {
        return $this->repo()->findByLogin($login);
    }

    public function authenticate(array $data): array
    {
        $input = $this->validateLogin($data);
        $user  = $this->findByLogin($input['login']);
        if (!$user || !$this->verifyPassword($input['password'], $user['password_hash'])) {
            throw new ValidationException('Invalid credentials', ['login' => 'Invalid credentials']);
        }
        return $user;
    }

    public function register(array $data): array
    {
        $clean = $this->validateRegister($data);
        if ($this->repo()->findByLogin($clean['login'])) {
            throw new ValidationException('Login is already taken', ['login' => 'Login is already taken']);
        }
        $this->repo()->create($clean);
        $user = $this->repo()->findByLogin($clean['login']);
        if (!$user) {
            throw new RuntimeException('Cannot create user');
        }
        return $user;
    }

    public function validateLogin(array $data): array
    {
        return (new AuthValidator())->login($data);
    }

    public function validateRegister(array $data): array
    {
        return (new AuthValidator())->register($data);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
