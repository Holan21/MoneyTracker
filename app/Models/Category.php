<?php

declare(strict_types=1);

class Category
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $type,
        public readonly string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:          (int)$data['id'],
            name:        (string)$data['name'],
            type:        (string)$data['type'],
            description: (string)($data['description'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'description' => $this->description,
        ];
    }

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }
}
