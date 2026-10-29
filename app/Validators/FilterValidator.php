<?php

declare(strict_types=1);

class FilterValidator extends Validator
{
    /**
     * @return array{q:string,type:string}
     */
    public function categories(array $data): array
    {
        $this->errors = [];

        $query = trim((string)($data['q'] ?? ''));
        $type  = (string)($data['type'] ?? '');

        if (!ValidationService::maxLength($query, 100)) {
            $this->addError('q', 'Search query is too long');
        }
        if ($type !== '' && !ValidationService::inList($type, ['income', 'expense'])) {
            $this->addError('type', 'Filter type must be income or expense');
        }

        $this->failIfErrors();

        return [
            'q'    => $query,
            'type' => $type,
        ];
    }

    /**
     * @return array{q:string,type:string,category_id:string,min_amount:string,max_amount:string}
     */
    public function transactions(array $data): array
    {
        $this->errors = [];

        $query      = trim((string)($data['q'] ?? ''));
        $type       = (string)($data['type'] ?? '');
        $categoryId = trim((string)($data['category_id'] ?? ''));
        $min        = trim((string)($data['min_amount'] ?? ''));
        $max        = trim((string)($data['max_amount'] ?? ''));

        if (!ValidationService::maxLength($query, 100)) {
            $this->addError('q', 'Search query is too long');
        }
        if ($type !== '' && !ValidationService::inList($type, ['income', 'expense'])) {
            $this->addError('type', 'Filter type must be income or expense');
        }
        if ($categoryId !== '' && (!ctype_digit($categoryId) || (int)$categoryId <= 0)) {
            $this->addError('category_id', 'Filter category is invalid');
        }
        if ($min !== '' && !is_numeric($min)) {
            $this->addError('min_amount', 'Min amount must be a number');
        }
        if ($max !== '' && !is_numeric($max)) {
            $this->addError('max_amount', 'Max amount must be a number');
        }
        if ($min !== '' && $max !== '' && is_numeric($min) && is_numeric($max) && (float)$min > (float)$max) {
            $this->addError('max_amount', 'Max amount must be greater than or equal to min amount');
        }

        $this->failIfErrors();

        return [
            'q'           => $query,
            'type'        => $type,
            'category_id' => $categoryId,
            'min_amount'  => $min,
            'max_amount'  => $max,
        ];
    }
}
