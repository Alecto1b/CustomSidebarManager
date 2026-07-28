<?php

namespace CustomSidebarManager\Support;

use Filament\Tables\Table;

final class CustomSidebarManagerPageResolver
{
    public static function usesFilamentFive(): bool
    {
        return class_exists(\Filament\Schemas\Schema::class)
            && method_exists(Table::class, 'recordActions');
    }

    /**
     * @return class-string
     */
    public static function adapter(): string
    {
        return self::usesFilamentFive()
            ? \CustomSidebarManager\Pages\Filament5CustomSidebarManagerPage::class
            : \CustomSidebarManager\Pages\Filament3CustomSidebarManagerPage::class;
    }
}
