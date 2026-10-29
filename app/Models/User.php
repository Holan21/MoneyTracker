<?php

declare(strict_types=1);

class User
{
    public function __construct(
        public readonly int    $id,
        public readonly string $login,
        public readonly string $passwordHash,
        public readonly string $name,
        public readonly string $role,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:           (int)$data['id'],
            login:        (string)$data['login'],
            passwordHash: (string)$data['password_hash'],
            name:         (string)$data['name'],
            role:         (string)$data['role'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'login' => $this->login,
            'name'  => $this->name,
            'role'  => $this->role,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
