# Filament Wirechat Plugin

A complete Filament v4 plugin for Wirechat - real-time messaging with private chats and group conversations.

## Requirements

- PHP 8.2 or higher
- Laravel 12.x
- Filament 4.x
- Database (MySQL, PostgreSQL, or SQLite)
- Broadcasting driver configured (Pusher, Reverb, Redis, or Ably)
- Queue driver configured (Database, Redis, or other)

## Installation

### Step 1: Install the Package

Install the plugin via Composer:

```bash
composer require adultdate/filament-wirechat
```

### Step 2: Run Installation Command

Run the installation command which will automatically:

- Publish the configuration file to `config/filament-wirechat.php`
- Publish all 6 database migrations
- Create storage symlink for file attachments
- Configure broadcasting settings in `.env` (if not already configured)
- Configure queue settings in `.env` (if not already configured)
- Set up Tailwind CSS source directives (attempts automatic configuration)
- Register `BroadcastServiceProvider` in `bootstrap/providers.php` (if needed)

```bash
php artisan wirechat:install
```

The command will:
- Prompt you to run migrations (confirm to proceed)
- Optionally create a standalone Wirechat panel at `/wirechat` (optional, for fullscreen chat experience)

**Note:** After running the install command, you still need to complete the manual steps below.

### Step 3: Update User Model (Required)

You must add the `InteractsWithWirechat` trait and implement the `WirechatUser` contract in your User model.

**File to edit:** `app/Models/User.php`

Add the following imports at the top:

```php
use Adultdate\Wirechat\Contracts\WirechatUser;
use Adultdate\Wirechat\Traits\InteractsWithWirechat;
```

Update your User class to implement the contract and use the trait:

```php
use HasFactory, InteractsWithWirechat, Notifiable;

class User extends Authenticatable implements WirechatUser
{
    // Your existing code...
}
```

Implement the required methods from the `WirechatUser` contract:

```php
/**
 * Determine if the user can create new groups.
 */
public function canCreateGroups(): bool
{
    // Customize this logic based on your requirements
    return true; // Or your custom logic
}

/**
 * Determine if the user can create new chats with other users.
 */
public function canCreateChats(): bool
{
    // Customize this logic based on your requirements
    return true; // Or your custom logic
}

/**
 * Determine if the user can access wirechat panel.
 */
public function canAccessWirechatPanel($panel): bool
{
    // Customize this logic based on your requirements
    // $panel can be either Filament\Panel or Adultdate\Wirechat\Panel
    return true; // Or your custom logic
}
```

**Complete example:**

```php
<?php

namespace App\Models;

use Adultdate\Wirechat\Contracts\WirechatUser;
use Adultdate\Wirechat\Traits\InteractsWithWirechat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements WirechatUser
{
    use HasFactory, InteractsWithWirechat, Notifiable;

    // ... your existing code ...

    public function canCreateGroups(): bool
    {
        return true;
    }

    public function canCreateChats(): bool
    {
        return true;
    }

    public function canAccessWirechatPanel($panel): bool
    {
        return true;
    }
}
```

### Step 4: Register the Plugin in Panel Provider (Required)

Add the plugin to your Filament panel provider.

**File to edit:** `app/Providers/Filament/AdminPanelProvider.php` (or your panel provider)

Add the import at the top:

```php
use AdultDate\FilamentWirechat\FilamentWirechatPlugin;
```

Add the plugin to your panel configuration:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugins([
            FilamentWirechatPlugin::make(),
        ])
        // ... rest of your panel configuration
}
```

**Complete example:**

```php
<?php

namespace App\Providers\Filament;

use AdultDate\FilamentWirechat\FilamentWirechatPlugin;
use Filament\Panel;
use Filament\PanelProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugins([
                FilamentWirechatPlugin::make(),
            ])
            // ... rest of your configuration
    }
}
```

**Note:** The plugin automatically discovers resources, pages, and widgets. You can optionally add specific pages to control their order or ensure they're registered:

```php
->pages([
    // Your existing pages...
    \AdultDate\FilamentWirechat\Filament\Pages\ChatDashboard::class,
    \AdultDate\FilamentWirechat\Filament\Pages\ChatsPage::class,
    \AdultDate\FilamentWirechat\Filament\Pages\ChatPage::class,
])
```

### Step 5: Configure Tailwind CSS (If Not Auto-Configured)

If the install command couldn't automatically add the Tailwind source directive, you need to add it manually.

**File to edit:** `resources/css/app.css`

Add the following line to ensure Wirechat views are included in your Tailwind build:

```css
@source '../../vendor/adultdate/filament-wirechat/resources/**/*.blade.php';
```

**Example:**

```css
@import 'tailwindcss';

@source '../views';
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../vendor/adultdate/filament-wirechat/resources/**/*.blade.php';
```

### Step 6: Verify BroadcastServiceProvider Registration

The install command should have automatically registered `BroadcastServiceProvider`, but verify it's included.

**File to check:** `bootstrap/providers.php`

Ensure `Illuminate\Broadcasting\BroadcastServiceProvider::class` is in the array:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    // ... other providers ...
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
];
```

### Step 7: Configure Broadcasting

Configure your broadcasting driver in `.env`. The plugin supports multiple broadcasting drivers:

**Option 1: Laravel Reverb (Recommended - Free and built-in)**

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

Start Reverb server:
```bash
php artisan reverb:start
```

**Option 2: Pusher**

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

**Option 3: Redis**

```env
BROADCAST_DRIVER=redis
```

**Option 4: Ably**

```env
BROADCAST_DRIVER=ably
ABLY_KEY=your-ably-key
```

### Step 8: Configure Queue

Ensure your queue connection is configured in `.env`:

```env
QUEUE_CONNECTION=database
```

Or use Redis:

```env
QUEUE_CONNECTION=redis
```

### Step 9: Configure Laravel Echo (Frontend)

Make sure your `resources/js/app.js` or main JavaScript file has Laravel Echo configured:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher', // or 'reverb', 'socket.io', etc.
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
    wsHost: window.location.hostname,
    wsPort: 6001,
    wssPort: 6001,
    disableStats: true,
});
```

For Reverb:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

### Step 10: Start Queue Worker

Start the queue worker to process background jobs:

```bash
php artisan queue:work
```

Or use Supervisor for production environments.

### Step 11: Build Frontend Assets

Build your frontend assets:

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

## Summary of Manual Steps

After running `php artisan wirechat:install`, you must manually:

1. ✅ **Update User Model** (`app/Models/User.php`)
   - Add `InteractsWithWirechat` trait
   - Implement `WirechatUser` contract
   - Add required methods: `canCreateGroups()`, `canCreateChats()`, `canAccessWirechatPanel()`

2. ✅ **Register Plugin** (`app/Providers/Filament/AdminPanelProvider.php`)
   - Import `FilamentWirechatPlugin`
   - Add `FilamentWirechatPlugin::make()` to `->plugins([])`

3. ✅ **Verify Tailwind CSS** (`resources/css/app.css`)
   - Ensure `@source '../../vendor/adultdate/filament-wirechat/resources/**/*.blade.php';` is present
   - (May have been auto-added by install command)

4. ✅ **Verify BroadcastServiceProvider** (`bootstrap/providers.php`)
   - Ensure `Illuminate\Broadcasting\BroadcastServiceProvider::class` is registered
   - (May have been auto-added by install command)

5. ✅ **Configure Broadcasting** (`.env`)
   - Set `BROADCAST_DRIVER`
   - Configure driver-specific settings

6. ✅ **Configure Queue** (`.env`)
   - Set `QUEUE_CONNECTION`

7. ✅ **Configure Laravel Echo** (`resources/js/app.js`)
   - Set up Echo configuration for your broadcasting driver

8. ✅ **Build Assets** (Terminal)
   - Run `npm run build` or `npm run dev`

9. ✅ **Start Queue Worker** (Terminal)
   - Run `php artisan queue:work`
   - For Reverb: Run `php artisan reverb:start`

## Configuration

After installation, you can customize the plugin behavior by editing `config/filament-wirechat.php`.

### Key Configuration Options

**Storage**
- Configure the storage disk and directory for file attachments
- Set file visibility (public/private)

**Broadcasting**
- Enable/disable real-time broadcasting
- Configure broadcasting driver
- Set message and notification queues

**Queue**
- Set queue connection for background jobs

**Theme**
- Customize color scheme for light and dark modes
- Override Filament panel colors

**Attachments**
- Configure allowed file types (media and documents)
- Set maximum upload size
- Set maximum number of uploads per message

**Search**
- Configure searchable user attributes for creating new chats

**Dashboard Route**
- Configure the home/redirect button URL in chat header
- Can be set to 'default', a route name, or a custom URL

**User Model**
- Override the default user model used for searching

For detailed configuration options, see the comments in `config/filament-wirechat.php`.

## Database Schema

The plugin creates the following database tables:

- `wirechat_conversations` - Stores conversation/chat records
- `wirechat_messages` - Stores all messages
- `wirechat_participants` - Tracks conversation participants
- `wirechat_attachments` - Stores file attachment metadata
- `wirechat_groups` - Stores group chat information
- `wirechat_actions` - Stores message actions (reactions, etc.)

## Features

- Private one-on-one chats
- Group conversations
- Real-time messaging with broadcasting
- File and media attachments
- Message search
- Dark mode support
- Customizable themes
- Message reactions and actions
- User presence indicators
- Typing indicators
- Navigation badge with unread message count
- SPA-style navigation for smooth user experience

## Usage

Once installed and configured, Wirechat will be available in your Filament panel. Users can:

1. Access chats via the navigation menu or widget
2. Create new private chats with other users
3. Create or join group conversations
4. Send messages with file attachments
5. Search through conversation history
6. Customize group settings (if admin)

## Troubleshooting

### Uninstalling the Package

If you need to uninstall the package:

1. **Remove from Composer:**
   ```bash
   composer remove adultdate/filament-wirechat
   ```

2. **Comment out ChatsPanelProvider** (if you're using standalone panel):
   In `bootstrap/providers.php`, comment out:
   ```php
   // App\Providers\Adultdate\ChatsPanelProvider::class,
   ```

3. **Remove published files** (optional):
   ```bash
   rm config/filament-wirechat.php
   rm database/migrations/2024_11_01_000001_create_wirechat_*.php
   # ... (remove all wirechat migrations)
   ```

4. **Clear caches:**
   ```bash
   composer dump-autoload
   php artisan config:clear
   php artisan cache:clear
   ```

### Class Redeclaration Error

If you see an error like:
```
Cannot redeclare class Adultdate\Wirechat\Facades\Wirechat
(previously declared in /path/to/plugins/filament-wirechat/src/Facades/Wirechat.php)
```

**This is a configuration issue** - the plugin is installed in **two locations** and both are being autoloaded:
1. Via Composer in `vendor/adultdate/filament-wirechat` ✅ (correct)
2. Also in a local `plugins/filament-wirechat` directory ❌ (causing conflict)

**🔧 Quick Fix:**

Run these commands in your Laravel project root:
```bash
# Remove the duplicate plugin directory
rm -rf plugins/filament-wirechat

# Clear Composer autoload cache
composer dump-autoload

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
```

**Alternative Solutions:**

If you need to keep the plugins directory for other packages, see the full troubleshooting guide in `TROUBLESHOOTING.md` for options to exclude it from autoload or use Composer's path repository.

### Migrations Not Running

If migrations were not run during installation, run them manually:

```bash
php artisan migrate
```

### Storage Symlink Missing

If file attachments are not accessible, create the storage symlink:

```bash
php artisan storage:link
```

### Broadcasting Not Working

1. Verify your broadcasting driver is correctly configured in `.env`
2. Ensure `BroadcastServiceProvider` is registered in `bootstrap/providers.php`
3. Check that Laravel Echo is properly configured in your JavaScript
4. For Reverb: Start the Reverb server with `php artisan reverb:start`
5. Check browser console for connection errors
6. Verify broadcasting channels are authorized (check `routes/channels.php`)

### Queue Not Processing

1. Verify `QUEUE_CONNECTION` is set in `.env`
2. Ensure queue worker is running: `php artisan queue:work`
3. For production, set up Supervisor or similar process manager
4. Check queue table exists if using database driver: `php artisan queue:table && php artisan migrate`

### Tailwind Styles Not Loading

1. Ensure `@source` directive is added to `resources/css/app.css`:
   ```css
   @source '../../vendor/adultdate/filament-wirechat/resources/**/*.blade.php';
   ```
2. Rebuild assets: `npm run build`
3. Verify Tailwind v4 is configured correctly

### User Model Errors

1. Ensure `InteractsWithWirechat` trait is added to your User model
2. Ensure `WirechatUser` contract is implemented
3. Verify all required methods are implemented: `canCreateGroups()`, `canCreateChats()`, `canAccessWirechatPanel()`
4. Check namespace imports are correct: `Adultdate\Wirechat\Contracts\WirechatUser` and `Adultdate\Wirechat\Traits\InteractsWithWirechat`

### Plugin Not Appearing

1. Verify plugin is registered in your panel provider's `->plugins([])` array
2. Clear config cache: `php artisan config:clear`
3. Clear view cache: `php artisan view:clear`
4. Rebuild assets: `npm run build`

### 403 Errors on Broadcasting

1. Check that broadcasting channels are properly authorized
2. Verify user authentication is working correctly
3. Check Laravel logs for detailed error messages
4. Ensure both panel guard and web guard are supported in channel authorization

## Testing

Run the test suite:

```bash
composer test
```

## License

MIT
