# Filament Draftable

Provides a <code>Draftable</code> trait that you can add to your Resource pages to allow saving data as a draft.
All data is stored in your browser's localStorage and is encrypted using Laravel's encryption.

## Installation

Install the package via composer:

```bash
composer require benjaminhansen/filament-draftable
```

## Usage

```php
<?php

namespace App\Filament\Resources\PostResource\Pages;

use BenjaminHansen\FilamentDraftable\Traits\Draftable;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use Draftable;

    // add exclusions to this array to prevent them from being saved in the draft
    public ?array $excludeFromDraft = ['password'];

    // add/modify this method
    protected function getFormActions(): array
    {
        return [
            // load the existing/default form actions
            ...parent::getFormActions(),

            // append the draftable actions
            // we can use all of Filament's Action methods to customize the draftable actions
            $this->saveDraftAction(),
                // ->icon('')
                // ->label('')

            $this->loadDraftAction(),
                // ->icon('')
                // ->label('')

            $this->deleteDraftAction(),
                // ->icon('')
                // ->label('')
                // ->requiresConfirmation()
        ]
    }
}
```
