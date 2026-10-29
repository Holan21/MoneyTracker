<?php

declare(strict_types=1);

class TransactionValidator extends Validator
{
    /** @return array{title:string,amount:float,type:string,category_id:int,occurred_at:string} */
    public function validate(array $data): array
    {
        $this->errors = [];

        $title      = trim((string)($data['title'] ?? ''));
        $amount     = $data['amount'] ?? null;
        $type       = (string)($data['type'] ?? '');
        $categoryId = (int)($data['category_id'] ?? 0);
        $occurredAt = trim((string)($data['occurred_at'] ?? ''));

        if (!ValidationService::required($title)) {
            $this->addError('title', 'Title is required');
        } elseif (!ValidationService::minLength($title, 2)) {
            $this->addError('title', 'Title is too short');
        } elseif (!ValidationService::maxLength($title, 200)) {
            $this->addError('title', 'Title is too long');
        }

        if (!ValidationService::positiveNumber($amount)) {
            $this->addError('amount', 'Amount must be positive');
        } elseif ((float)$amount > 99999999.99) {
            $this->addError('amount', 'Amount is too large');
        }

        if (!ValidationService::inList($type, ['income', 'expense'])) {
            $this->addError('type', 'Type must be income or expense');
        }

        if ($categoryId <= 0) {
            $this->addError('category_id', 'Category is required');
        }

        if ($occurredAt === '') {
            $occurredAt = date('Y-m-d H:i:s');
        } elseif (!strtotime($occurredAt)) {
            $this->addError('occurred_at', 'Invalid date');
        } else {
            $occurredAt = date('Y-m-d H:i:s', strtotime($occurredAt));
        }

        $this->failIfErrors();

        return [
            'title'       => $title,
            'amount'      => round((float)$amount, 2),
            'type'        => $type,
            'category_id' => $categoryId,
            'occurred_at' => $occurredAt,
        ];
    }
}
