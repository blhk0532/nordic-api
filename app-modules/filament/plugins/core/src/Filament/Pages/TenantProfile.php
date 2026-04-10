<?php

namespace Cachet\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class TenantProfile extends BaseEditProfile
{
    public function getTitle(): string|Htmlable
    {
        return __('cachet::navigation.user.items.edit_profile');
    }

    public static function isSimple(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
