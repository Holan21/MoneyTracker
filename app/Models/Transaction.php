<?php

declare(strict_types=1);

class Transaction
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $categoryId,
        public readonly string  $categoryName,
        public readonly string  $title,
        public readonly float   $amount,
        public readonly string  $type,
        public readonly string  $occurredAt,
        public readonly ?string $receipt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:           (int)$data['id'],
            categoryId:   (int)$data['category_id'],
            categoryName: (string)($data['category_name'] ?? ''),
            title:        (string)$data['title'],
            amount:       (float)$data['amount'],
            type:         (string)$data['type'],
            occurredAt:   (string)$data['occurred_at'],
            receipt:      isset($data['receipt']) ? (string)$data['receipt'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'category_id'   => $this->categoryId,
            'category_name' => $this->categoryName,
            'title'         => $this->title,
            'amount'        => $this->amount,
            'type'          => $this->type,
            'occurred_at'   => $this->occurredAt,
            'receipt'       => $this->receipt,
        ];
    }

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }
}
