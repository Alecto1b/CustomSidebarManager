<?php

namespace CustomSidebarManager\Pages;

use App\Tables\Columns\IndexColumn;
use CustomSidebarManager\Models\CustomSidebar;
use CustomSidebarManager\Pages\Concerns\ManagesCustomSidebars;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class Filament3CustomSidebarManagerPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use ManagesCustomSidebars;

    protected static ?string $title = 'Custom Sidebar Manager';

    protected static string $view = 'CustomSidebarManager::page';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'custom-sidebar-manager-page';

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
            ->actions([
                TableAction::make('edit')
                    ->label('Edit')
                    ->modalWidth('3xl')
                    ->fillForm(fn (Model $record): array => $record->attributesToArray())
                    ->form($this->getCustomFormSchema())
                    ->action(fn (Model $record, array $data) => $this->updateCustomSidebar($record, $data)),
                TableAction::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Model $record) => $this->deleteCustomSidebar($record)),
            ])
            ->bulkActions([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Custom Sidebar')
                ->action(fn (array $data) => $this->createCustomSidebar($data))
                ->modalWidth('3xl')
                ->form($this->getCustomFormSchema()),
        ];
    }
}
