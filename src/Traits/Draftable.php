<?php

namespace BenjaminHansen\FilamentDraftable\Traits;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

trait Draftable
{
    // keys that should be excluded from being saved in the draft
    public ?array $excludeFromDraft = [];

    public function saveDraftAction(): Action
    {
        // the key will be the slug of the current URL
        $storageKey = str_slug($this->getUrl());
        $data_to_store = array_diff_key($this->data, array_flip($this->excludeFromDraft));
        $state = json_encode($data_to_store);
        $state = encrypt($state);

        return Action::make('draftableSave')
            ->label(__('Save Draft'))
            ->icon('heroicon-o-check')
            ->action(function ($livewire) use ($state, $storageKey) {
                $livewire->js(
                    "localStorage.setItem('{$storageKey}', '{$state}');"
                );

                // Send a notification
                Notification::make()
                    ->title(__('Draft saved'))
                    ->success()
                    ->send();
            });
    }

    public function loadDraftAction(): Action
    {
        // the key will be the slug of the current URL
        $storageKey = str_slug($this->getUrl());

        return Action::make('draftableLoad')
            ->label(__('Load Draft'))
            ->icon('heroicon-o-arrow-up-tray')
            ->action(function ($livewire) use ($storageKey) {
                $livewire->js(
                    "let draft = localStorage.getItem('{$storageKey}');
                    if(draft) {
                        Livewire.dispatch('draftLoaded', {draft: draft});
                    }"
                );

                // Send a notification
                Notification::make()
                    ->title(__('Draft loaded'))
                    ->success()
                    ->send();
            });
    }

    public function deleteDraftAction(): Action
    {
        // the key will be the slug of the current URL
        $storageKey = str_slug($this->getUrl());

        return Action::make('draftableDelete')
            ->label(__('Delete Draft'))
            ->icon('heroicon-o-trash')
            ->action(function($livewire) use ($storageKey) {
                $livewire->js("localStorage.removeItem('{$storageKey}');");

                Notification::make()
                    ->title(__('Draft deleted'))
                    ->success()
                    ->send();
            });
    }

    #[On('draftLoaded')]
    public function draftLoaded($draft)
    {
        $draft = decrypt($draft);
        $draft = json_decode($draft, true);

        // load the draft data back into the data object of the form
        foreach ($draft as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                $this->data[$key] = $value;
            }
        }

        Notification::make()
            ->title(__('Draft loaded'))
            ->success()
            ->send();
    }
}
