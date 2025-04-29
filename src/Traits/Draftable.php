<?php

namespace BenjaminHansen\FilamentDraftable\Traits;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cookie;

trait Draftable
{
    public ?array $excludeFromDraft = [];
    public ?int $saveDraftFor = null;

    public function saveDraft(): Action
    {
        // the key will be the slug of the current URL
        $storageKey = str_slug($this->getUrl());
        $data = $this->data;
        $data_to_store = array_diff_key($data, array_flip($this->excludeFromDraft));
        $state = json_encode($data_to_store);

        return Action::make('draftableSave')
            ->label('Save Draft')
            ->icon('heroicon-o-check')
            ->action(function () use ($storageKey, $state) {
                // store in a cookie
                Cookie::queue($storageKey, $state, $this->saveDraftFor ?? 9999999);

                // Send a notification
                Notification::make()
                    ->title('Draft Saved')
                    ->success()
                    ->send();
            });
    }

    public function loadDraft(): Action
    {
        // the key will be the slug of the current URL
        $storageKey = str_slug($this->getUrl());

        return Action::make('draftableLoad')
            ->label('Load Draft')
            ->icon('heroicon-o-arrow-up-tray')
            ->action(function () use ($storageKey) {
                // get the draft data from the cookie
                $data = Cookie::get($storageKey);

                if ($data) {
                    $parsed = json_decode($data, true);
                    foreach ($parsed as $key => $value) {
                        if (array_key_exists($key, $this->data)) {
                            $this->data[$key] = $value;
                        }
                    }

                    // remove the cookie
                    Cookie::forget($storageKey);

                    // Send a notification
                    Notification::make()
                        ->title('Draft Loaded')
                        ->success()
                        ->send();
                }
            })
            ->disabled(function () use ($storageKey) {
                return ! Cookie::has($storageKey);
            });
    }
}
