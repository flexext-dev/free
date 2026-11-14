<?php
declare(strict_types=1);

namespace flexext\aiseolite\models;

use craft\base\Model;

class Settings extends Model
{
    public bool $enabled = true;
    public bool $enableLlmsTxt = true;
    public bool $enableJsonLd = true;
    public int $entryLimit = 30;
    public string $siteSummary = '';

    protected function defineRules(): array
    {
        return [
            [['enabled', 'enableLlmsTxt', 'enableJsonLd'], 'boolean'],
            [['entryLimit'], 'integer', 'min' => 5, 'max' => 50],
            [['siteSummary'], 'string', 'max' => 400],
        ];
    }
}
