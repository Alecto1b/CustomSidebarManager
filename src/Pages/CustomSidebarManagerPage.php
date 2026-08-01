<?php

namespace CustomSidebarManager\Pages;

use App\Facades\Plugin;
use App\Forms\Components\TinyEditor;
use App\Tables\Columns\IndexColumn;
use CustomSidebarManager\Models\CustomSidebar;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomSidebarManagerPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $title = 'Custom Sidebar Manager';

    protected string $view = 'CustomSidebarManager::page';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'custom-sidebar-manager-page';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('viewAny', \App\Models\Plugin::class);
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            static::getUrl() => 'Plugins',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CustomSidebar::query())
            ->columns([
                IndexColumn::make('no'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->authorize(fn (): bool => auth()->user()?->can('Plugin:update') ?? false)
                    ->modalWidth(Width::ThreeExtraLarge)
                    ->fillForm(fn (Model $record): array => $record->attributesToArray())
                    ->schema($this->getFormSchemas())
                    ->action(function (Model $record, array $data): void {
                        abort_unless(auth()->user()?->can('Plugin:update') ?? false, 403);

                        $plugin = Plugin::getPlugin('CustomSidebarManager');
                        $customSidebars = $plugin?->getSetting('custom_sidebars', []) ?? [];
                        if (! is_array($customSidebars)) {
                            $customSidebars = [];
                        }

                        foreach ($customSidebars as $key => $sidebar) {
                            if (is_array($sidebar) && isset($sidebar['id']) && (string) $sidebar['id'] === (string) $record->id) {
                                $customSidebars[$key] = array_merge($sidebar, [
                                    'name' => (string) ($data['name'] ?? ''),
                                    'show_name' => (bool) ($data['show_name'] ?? false),
                                    'content' => (string) ($data['content'] ?? ''),
                                ]);
                            }
                        }

                        $plugin?->updateSetting('custom_sidebars', array_values($customSidebars));
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->authorize(fn (): bool => auth()->user()?->can('Plugin:update') ?? false)
                    ->requiresConfirmation()
                    ->action(function (Model $record): void {
                        abort_unless(auth()->user()?->can('Plugin:update') ?? false, 403);

                        $plugin = Plugin::getPlugin('CustomSidebarManager');
                        $customSidebars = $plugin?->getSetting('custom_sidebars', []) ?? [];
                        if (! is_array($customSidebars)) {
                            $customSidebars = [];
                        }

                        $remaining = [];
                        foreach ($customSidebars as $sidebar) {
                            if (is_array($sidebar) && isset($sidebar['id']) && (string) $sidebar['id'] === (string) $record->id) {
                                continue;
                            }
                            $remaining[] = $sidebar;
                        }

                        $plugin?->updateSetting('custom_sidebars', array_values($remaining));
                    }),
            ])
            ->bulkActions([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Custom Sidebar')
                ->authorize(fn (): bool => auth()->user()?->can('Plugin:update') ?? false)
                ->modalWidth(Width::ThreeExtraLarge)
                ->schema($this->getFormSchemas())
                ->action(function (array $data): void {
                    abort_unless(auth()->user()?->can('Plugin:update') ?? false, 403);

                    $plugin = Plugin::getPlugin('CustomSidebarManager');
                    $customSidebars = $plugin?->getSetting('custom_sidebars', []) ?? [];
                    if (! is_array($customSidebars)) {
                        $customSidebars = [];
                    }

                    $data['id'] = (string) Str::uuid();
                    $customSidebars[] = $data;

                    $plugin?->updateSetting('custom_sidebars', array_values($customSidebars));
                }),
        ];
    }

    /**
     * @return array<int, object>
     */
    public function getFormSchemas(): array
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
}
