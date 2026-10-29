<?php

declare(strict_types=1);

class CategoryService
{
    private ?CategoryRepository $repo = null;

    private function repo(): CategoryRepository
    {
        return $this->repo ??= new CategoryRepository();
    }

    public function all(): array
    {
        return $this->repo()->all();
    }

    public function find(int $id): array|false
    {
        return $this->repo()->find($id);
    }

    public function create(array $data): void
    {
        $clean = $this->validate($data);
        $this->repo()->create($clean);
        TextStorage::sync();
    }

    public function update(int $id, array $data): void
    {
        $clean = $this->validate($data);
        $this->repo()->update($id, $clean);
        TextStorage::sync();
    }

    public function delete(int $id): void
    {
        $this->repo()->delete($id);
        TextStorage::sync();
    }

    public function validate(array $data): array
    {
        return (new CategoryValidator())->validate($data);
    }

    public function filter(array $items, array $filters): array
    {
        $filters = (new FilterValidator())->categories($filters);
        $query   = $filters['q'];
        $type    = $filters['type'];

        return array_values(array_filter(
            $items,
            static function (array $item) use ($query, $type): bool {
                if ($query !== '' && mb_stripos((string)$item['name'], $query) === false) {
                    return false;
                }
                if ($type !== '' && ($item['type'] ?? '') !== $type) {
                    return false;
                }
                return true;
            }
        ));
    }
}
