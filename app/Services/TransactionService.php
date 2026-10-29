<?php

declare(strict_types=1);

class TransactionService
{
    private ?TransactionRepository $repo = null;
    private ?CategoryRepository $categories = null;

    private function repo(): TransactionRepository
    {
        return $this->repo ??= new TransactionRepository();
    }

    private function categories(): CategoryRepository
    {
        return $this->categories ??= new CategoryRepository();
    }

    public function all(): array
    {
        return $this->repo()->all();
    }

    public function find(int $id): array|false
    {
        return $this->repo()->find($id);
    }

    public function byCategory(int $id): array
    {
        return $this->repo()->byCategory($id);
    }

    public function create(array $data, array $file = []): void
    {
        $clean = $this->validate($data);
        $this->assertCategoryMatches($this->categories()->find($clean['category_id']), $clean['type']);
        if (!empty($file['name'])) {
            $clean['receipt'] = (new FileUploadService())->upload($file, (string)config('upload_dir'));
        }
        $this->repo()->create($clean);
        TextStorage::sync();
    }

    public function update(array $existing, array $data, array $file = []): void
    {
        $clean = $this->validate($data);
        $this->assertCategoryMatches($this->categories()->find($clean['category_id']), $clean['type']);
        $clean['receipt'] = $existing['receipt'] ?? null;
        if (!empty($file['name'])) {
            $clean['receipt'] = (new FileUploadService())->upload($file, (string)config('upload_dir'));
        }
        $this->repo()->update((int)$existing['id'], $clean);
        TextStorage::sync();
    }

    public function delete(int $id): void
    {
        $this->repo()->delete($id);
        TextStorage::sync();
    }

    public function validate(array $data): array
    {
        return (new TransactionValidator())->validate($data);
    }

    public function assertCategoryMatches(array|false|null $category, string $type): void
    {
        if (!$category) {
            throw new ValidationException('Category not found', ['category_id' => 'Category not found']);
        }
        if (($category['type'] ?? '') !== $type) {
            throw new ValidationException(
                'Transaction type must match the category type',
                ['type' => 'Transaction type must match the category type']
            );
        }
    }

    public function filter(array $items, array $filters): array
    {
        $filters    = (new FilterValidator())->transactions($filters);
        $query      = $filters['q'];
        $type       = $filters['type'];
        $categoryId = $filters['category_id'];
        $min        = $filters['min_amount'];
        $max        = $filters['max_amount'];

        return array_values(array_filter($items, static function (array $item) use ($query, $type, $categoryId, $min, $max): bool {
            if ($query !== '' && mb_stripos((string)$item['title'], $query) === false) {
                return false;
            }
            if ($type !== '' && ($item['type'] ?? '') !== $type) {
                return false;
            }
            if ($categoryId !== '' && (int)$item['category_id'] !== (int)$categoryId) {
                return false;
            }
            if ($min !== '' && is_numeric($min) && (float)$item['amount'] < (float)$min) {
                return false;
            }
            if ($max !== '' && is_numeric($max) && (float)$item['amount'] > (float)$max) {
                return false;
            }
            return true;
        }));
    }

    public function summarize(array $items): array
    {
        $income  = 0.0;
        $expense = 0.0;
        foreach ($items as $item) {
            $amount = (float)$item['amount'];
            if (($item['type'] ?? '') === 'income') {
                $income += $amount;
            } else {
                $expense += $amount;
            }
        }

        $total       = $income + $expense;
        $incomeShare = $total > 0 ? round($income / $total * 100, 2) : 0.0;

        return [
            'income'       => round($income, 2),
            'expense'      => round($expense, 2),
            'balance'      => round($income - $expense, 2),
            'income_share' => $incomeShare,
        ];
    }
    
    public function recentByType(array $items, string $type, int $limit = 7): array
    {
        if (!ValidationService::inList($type, ['income', 'expense'])) {
            throw new ValidationException('Unknown transaction type');
        }

        $filtered = array_values(array_filter(
            $items,
            static fn(array $item): bool => ($item['type'] ?? '') === $type
        ));

        return array_slice($filtered, 0, $limit);
    }
}
