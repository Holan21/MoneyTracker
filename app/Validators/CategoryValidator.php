<?php

declare(strict_types=1);

class CategoryValidator extends Validator
{
    /** @return array{name:string,type:string,description:string} */
    public function validate(array $data): array
    {
        $this->errors = [];

        $name        = trim((string)($data['name'] ?? ''));
        $type        = (string)($data['type'] ?? '');
        $description = trim((string)($data['description'] ?? ''));

        if (!ValidationService::required($name)) {
            $this->addError('name', 'Category name is required');
        } elseif (!ValidationService::minLength($name, 2)) {
            $this->addError('name', 'Category name is too short');
        } elseif (!ValidationService::maxLength($name, 120)) {
            $this->addError('name', 'Category name is too long');
        }

        if (!ValidationService::inList($type, ['income', 'expense'])) {
            $this->addError('type', 'Category type must be income or expense');
        }

        if (!ValidationService::maxLength($description, 500)) {
            $this->addError('description', 'Description is too long');
        }

        $this->failIfErrors();

        return [
            'name'        => $name,
            'type'        => $type,
            'description' => $description,
        ];
    }
}
