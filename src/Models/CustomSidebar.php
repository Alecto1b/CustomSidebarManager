<?php

namespace CustomSidebarManager\Models;

use App\Facades\Plugin;
use CustomSidebarManager\Support\CustomSidebarStore;
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

        return (new CustomSidebarStore($plugin))->rowsForModel();
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
