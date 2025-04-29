<?php

namespace BenjaminHansen\FilamentDraftable\Traits;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cookie;

trait Draftable
{
    // keys that should be excluded from being saved in the draft
    public ?array $excludeFromDraft = [];

    // the number of seconds we should set the cookie to be valid for
    public int $saveDraftFor = 9999999;

    public function saveDraftAction(): Action
    {
        // the key will be the slug of the current URL
        $storageKey = str_slug($this->getUrl());
        $data_to_store = array_diff_key($this->data, array_flip($this->excludeFromDraft));
        $state = json_encode($data_to_store);

        return Action::make('draftableSave')
            ->label(__('Save Draft'))
            ->icon('heroicon-o-check')
            ->action(function () use ($storageKey, $state) {
                // store in a cookie
                Cookie::queue($storageKey, $state, $this->saveDraftFor);

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
            ->action(function () use ($storageKey) {
                // get the draft data from the cookie
                if ($data = Cookie::get($storageKey)) {
                    $parsed = json_decode($data, true);

                    // load the draft data back into the data object of the form
                    foreach ($parsed as $key => $value) {
                        if (array_key_exists($key, $this->data)) {
                            $this->data[$key] = $value;
                        }
                    }

                    // remove the cookie, just for good measure
                    Cookie::forget($storageKey);

                    // Send a success notification because we loaded the draft
                    Notification::make()
                        ->title(__('Draft loaded'))
                        ->success()
                        ->send();
                } else {
                    // send a warning notification because we have no draft to load
                    Notification::make()
                        ->title(__('No draft available to load'))
                        ->warning()
                        ->send();
                }
            });
    }
}
