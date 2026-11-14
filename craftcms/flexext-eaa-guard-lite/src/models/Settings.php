<?php
declare(strict_types=1);

namespace flexext\eaaguardlite\models;

use craft\base\Model;

class Settings extends Model
{
    public bool $enabled = true;
    public bool $enableStatementRoute = true;
    public string $organizationName = '';
    public string $contactEmail = '';

    protected function defineRules(): array
    {
        return [
            [['enabled', 'enableStatementRoute'], 'boolean'],
            [['organizationName'], 'string', 'max' => 160],
            [['contactEmail'], 'email'],
        ];
    }
}
