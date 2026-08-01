<?php

namespace CustomSidebarManager\Models;

use App\Facades\Plugin;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class CustomSidebar extends Model
{
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $schema = [
        'id' => 'string',
        'name' => 'string',
        'show_name' => 'boolean',
        'content' => 'string',
    ];

    public function getRows(): array
    {
        $plugin = Plugin::getPlugin('CustomSidebarManager');
        $rows = $plugin?->getSetting('custom_sidebars', []) ?? [];

        if (! is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['id'])) {
                continue;
            }

            $result[] = [
                'id' => (string) $row['id'],
                'name' => (string) ($row['name'] ?? ''),
                'show_name' => (bool) ($row['show_name'] ?? false),
                'content' => (string) ($row['content'] ?? ''),
            ];
        }

        return $result;
    }

    protected function sushiShouldCache(): bool
    {
        return false;
    }
}
