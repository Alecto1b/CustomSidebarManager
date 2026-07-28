<?php

namespace CustomSidebarManager\Pages\Concerns;

use App\Facades\Plugin;
use App\Forms\Components\TinyEditor;
use CustomSidebarManager\Support\CustomSidebarAuthorization;
use CustomSidebarManager\Support\CustomSidebarStore;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

trait ManagesCustomSidebars
{
    public static function canAccess(): bool
    {
        return CustomSidebarAuthorization::canManage();
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            static::getUrl() => 'Plugins',
        ];
    }

    /**
     * @return array<int, object>
     */
    public function getCustomFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Name')
                ->required(),
            Toggle::make('show_name')
                ->label('Show the name of this sidebar above the content?')
                ->default(false),
            TinyEditor::make('content')
                ->label('Content')
                ->minHeight(300)
                ->plugins('advlist autoresize codesample directionality emoticons fullscreen hr image imagetools link lists media table toc wordcount code')
                ->toolbar('undo redo removeformat | formatselect fontsizeselect | bold italic | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist | blockquote table hr | image link fullscreen | code')
                ->helperText('Content of the sidebar.'),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function createCustomSidebar(array $data): void
    {
        $this->customSidebarStore()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function updateCustomSidebar(object $record, array $data): void
    {
        $this->customSidebarStore()->update($record->getKey(), $data);
    }

    protected function deleteCustomSidebar(object $record): void
    {
        $this->customSidebarStore()->delete($record->getKey());
    }

    protected function customSidebarStore(): CustomSidebarStore
    {
        return new CustomSidebarStore(Plugin::getPlugin('CustomSidebarManager'));
    }
}
