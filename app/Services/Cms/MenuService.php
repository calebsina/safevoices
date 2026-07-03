<?php

namespace App\Services\Cms;

use App\Models\Cms\Menu;
use App\Services\BaseService;

class MenuService extends BaseService
{
    protected string $model = Menu::class;

    protected array $with = ['items.translations', 'items.children.translations', 'items.page:id,slug'];

    public function byKey(string $key): Menu
    {
        return $this->query()->where('key', $key)->where('is_active', true)->firstOrFail();
    }
}
