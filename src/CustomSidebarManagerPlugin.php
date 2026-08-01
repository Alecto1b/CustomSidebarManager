<?php

namespace CustomSidebarManager;

use App\Classes\Plugin;
use App\Facades\SidebarFacade;
use CustomSidebarManager\Pages\CustomSidebarManagerPage;
use Filament\Panel;

class CustomSidebarManagerPlugin extends Plugin
{
    public function boot(): void
    {
        $customSidebars = $this->getSetting('custom_sidebars', []);
        if (! is_array($customSidebars)) {
            $customSidebars = [];
        }

        $customBlocks = [];
        foreach ($customSidebars as $sidebar) {
            if (! is_array($sidebar) || empty($sidebar['id'])) {
                continue;
            }

            $customBlocks[] = new CustomSidebarBlock(
                id: (string) $sidebar['id'],
                name: (string) ($sidebar['name'] ?? ''),
                content: isset($sidebar['content']) ? (string) $sidebar['content'] : null,
                showName: (bool) ($sidebar['show_name'] ?? false),
            );
        }

        SidebarFacade::register($customBlocks);
    }

    public function onPanel(Panel $panel): void
    {
        $panel->pages([
            CustomSidebarManagerPage::class,
        ]);
    }

    public function getPluginPage(): ?string
    {
        if (! auth()->user()?->can('viewAny', \App\Models\Plugin::class)) {
            return null;
        }

        try {
            return CustomSidebarManagerPage::getUrl();
        } catch (\Throwable) {
            return null;
        }
    }
}
