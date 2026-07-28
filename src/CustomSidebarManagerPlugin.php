<?php

namespace CustomSidebarManager;

use App\Classes\Plugin;
use App\Facades\SidebarFacade;
use CustomSidebarManager\Pages\CustomSidebarManagerPage;
use CustomSidebarManager\Support\CustomSidebarStore;
use Filament\Panel;

class CustomSidebarManagerPlugin extends Plugin
{
    public function boot()
    {
        $customBlocks = array_map(
            fn (array $sidebar): CustomSidebarBlock => new CustomSidebarBlock(
                (string) $sidebar['id'],
                (string) $sidebar['name'],
                $sidebar['content'],
                (bool) $sidebar['show_name'],
            ),
            (new CustomSidebarStore($this))->rowsForRegistration(),
        );

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
        try {
            return CustomSidebarManagerPage::getUrl();
        } catch (\Throwable) {
            return null;
        }
    }
}
