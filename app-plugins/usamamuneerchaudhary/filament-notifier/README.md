# Filament Notifier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/usamamuneerchaudhary/filament-notifier?style=flat-square)](https://packagist.org/packages/usamamuneerchaudhary/filament-notifier)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/usamamuneerchaudhary/filament-notifier/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/usamamuneerchaudhary/filament-notifier/?branch=main)
[![CodeFactor](https://www.codefactor.io/repository/github/usamamuneerchaudhary/filament-notifier/badge)](https://www.codefactor.io/repository/github/usamamuneerchaudhary/filament-notifier)
[![Build Status](https://scrutinizer-ci.com/g/usamamuneerchaudhary/filament-notifier/badges/build.png?b=main)](https://scrutinizer-ci.com/g/usamamuneerchaudhary/filament-notifier/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/usamamuneerchaudhary/filament-notifier/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)
[![Total Downloads](https://img.shields.io/packagist/dt/usamamuneerchaudhary/filament-notifier?style=flat-square)](https://packagist.org/packages/usamamuneerchaudhary/filament-notifier)
[![Licence](https://img.shields.io/packagist/l/usamamuneerchaudhary/filament-notifier?style=flat-square)](https://github.com/usamamuneerchaudhary/filament-notifier/blob/HEAD/LICENSE.md)

A powerful notification system for FilamentPHP that handles multi-channel notifications with template management, scheduling, and real-time delivery. Built for developers who need enterprise-grade notifications without the complexity.

<div align="center">
  <img src="public/images/filament notifier.gif" alt="Filament Notifier Banner" width="100%">
</div>

## Features

- 🚀 **Multi-Channel Support**: Email, SMS, Slack, Discord, Push, and more
- 📝 **Template Management**: Create and manage notification templates with variable support
- ⏰ **Scheduled Notifications**: Send notifications at specific times
- 🎯 **Event-Driven**: Trigger notifications based on application events
- 👥 **User Preferences**: Allow users to control their notification preferences via REST API
- 📊 **Analytics Dashboard**: Comprehensive dashboard with engagement metrics, charts, and insights
- 📈 **Email Tracking**: Track email opens and link clicks with analytics
- ⚡ **Rate Limiting**: Built-in rate limiting to prevent abuse (per minute, hour, day)
- 🔧 **Easy Configuration**: Simple setup with comprehensive configuration options
- 🧪 **Fully Tested**: Comprehensive test suite for reliability

## Installation

### 1. Install the Package

```bash
composer require usamamuneerchaudhary/filament-notifier
```

### 2. Run the Installation Command

```bash
php artisan notifier:install
```

This command will:
- Publish the configuration file
- Run the necessary migrations
- Create sample notification channels and templates
- Set up the basic structure

### 3. Register the Plugin

Add the plugin to your Filament panel configuration:

```php
use Usamamuneerchaudhary\Notifier\FilamentNotifierPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentNotifierPlugin::make(),
        ]);
}
```

After registration, you'll have access to:
- **Notifier Dashboard** - Comprehensive analytics and metrics
- **Notification Settings** - Configure preferences, analytics, and rate limiting
- **Notification Resources** - Manage channels, events, templates, and notifications

### 4. Configure Channels

Update your `.env` file with your notification channel settings:

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Your App"

# Notifier Package Settings
NOTIFIER_EMAIL_ENABLED=true
NOTIFIER_SLACK_ENABLED=false
NOTIFIER_SMS_ENABLED=false

# Slack Configuration
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
SLACK_CHANNEL=#notifications

# SMS Configuration (Twilio)
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_PHONE_NUMBER=+1234567890
```

## Usage

### Basic Notification Sending

```php
use Usamamuneerchaudhary\Notifier\Facades\Notifier;

// Send a notification using the facade
Notifier::send($user, 'user.registered', [
    'name' => $user->name,
    'email' => $user->email,
]);

// Or using the service directly
$notifier = app('notifier');
$notifier->send($user, 'user.registered', [
    'name' => $user->name,
    'email' => $user->email,
]);
```

### Using Service Facades

The package provides convenient facades for accessing services:

```php
use Usamamuneerchaudhary\Notifier\Facades\Preference;
use Usamamuneerchaudhary\Notifier\Facades\Analytics;
use Usamamuneerchaudhary\Notifier\Facades\UrlTracking;
use Usamamuneerchaudhary\Notifier\Facades\NotificationRepo;

// Get user preferences
$preferences = Preference::getUserPreferences($user, 'user.registered');

// Check analytics status
if (Analytics::isEnabled()) {
    Analytics::trackOpen($notification);
}

// Safely redirect URLs
return UrlTracking::safeRedirect('https://example.com');

// Find notification by token
$notification = NotificationRepo::findByToken($token);
```

**Note:** After installing the package, run `composer dump-autoload` to register the facades.

### Dependency Injection (Recommended)

For better testability, use dependency injection in your controllers and services:

```php
use Usamamuneerchaudhary\Notifier\Services\PreferenceService;
use Usamamuneerchaudhary\Notifier\Services\AnalyticsService;

class MyController extends Controller
{
    public function __construct(
        protected PreferenceService $preferenceService,
        protected AnalyticsService $analyticsService
    ) {}

    public function index()
    {
        $preferences = $this->preferenceService->getUserPreferences($user, 'event.key');
        
        if ($this->analyticsService->isEnabled()) {
            // Do something
        }
    }
}
```

### Scheduled Notifications

```php
use Carbon\Carbon;

// Schedule a notification for later
$notifier->schedule($user, 'reminder.email', Carbon::now()->addDays(7), [
    'task_name' => 'Complete project review',
]);
```

### Event-Based Notifications

Register events in your `config/notifier.php`:

```php
'events' => [
    'user.registered' => [
        'channels' => ['email', 'slack'],
        'template' => 'welcome-email',
    ],
    'order.completed' => [
        'channels' => ['email', 'sms'],
        'template' => 'order-confirmation',
    ],
],
```

### Creating Templates

Templates can be created through the Filament admin panel or programmatically:

```php
use Usamamuneerchaudhary\Notifier\Models\NotificationTemplate;

NotificationTemplate::create([
    'title' => 'Welcome Email',
    'key' => 'welcome-email',
    'type' => 'email',
    'subject' => 'Welcome to {{app_name}}, {{name}}!',
    'content' => 'Hi {{name}},\n\nWelcome to {{app_name}}! We\'re excited to have you on board.',
    'variables' => [
        'name' => 'User\'s full name',
        'app_name' => 'Application name',
    ],
]);
```

### Custom Channel Drivers

Create custom channel drivers by implementing the `ChannelDriverInterface`:

```php
use Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\ChannelDriverInterface;
use Usamamuneerchaudhary\Notifier\Models\Notification;

class CustomChannelDriver implements ChannelDriverInterface
{
    public function send(Notification $notification): bool
    {
        // Your custom sending logic here
        return true;
    }

    public function validateSettings(array $settings): bool
    {
        // Validate your channel settings
        return !empty($settings['api_key'] ?? null);
    }
}
```

### User Preferences API

The package provides a simple REST API for users to manage their notification preferences. All endpoints require authentication and respect the `allow_override` setting configured in the admin panel.

#### Get All User Preferences

```http
GET /api/notifier/preferences
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": [
    {
      "event_key": "user.registered",
      "event_name": "User Registered",
      "event_group": "User",
      "description": "Sent when a new user registers",
      "channels": {
        "email": true,
        "sms": false,
        "push": true
      }
    }
  ]
}
```

#### Get Available Events and Channels

```http
GET /api/notifier/preferences/available
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "events": [
      {
        "key": "user.registered",
        "name": "User Registered",
        "group": "User",
        "description": "Sent when a new user registers",
        "default_channels": ["email"]
      }
    ],
    "channels": [
      {
        "type": "email",
        "title": "Email",
        "icon": "heroicon-o-envelope"
      },
      {
        "type": "sms",
        "title": "SMS",
        "icon": "heroicon-o-device-phone-mobile"
      }
    ]
  }
}
```

#### Get Preference for Specific Event

```http
GET /api/notifier/preferences/{eventKey}
Authorization: Bearer {token}
```

**Example:**
```http
GET /api/notifier/preferences/user.registered
```

**Response:**
```json
{
  "data": {
    "event_key": "user.registered",
    "event_name": "User Registered",
    "event_group": "User",
    "description": "Sent when a new user registers",
    "channels": {
      "email": true,
      "sms": false
    }
  }
}
```

#### Update Preference for Event

```http
PUT /api/notifier/preferences/{eventKey}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "channels": {
    "email": true,
    "sms": true,
    "push": false
  },
  "settings": {}
}
```

**Response:**
```json
{
  "data": {
    "event_key": "user.registered",
    "event_name": "User Registered",
    "channels": {
      "email": true,
      "sms": true,
      "push": false
    }
  },
  "message": "Preferences updated successfully."
}
```

**Error Responses:**

- `403 Forbidden` - User preference override is disabled by administrator
- `422 Unprocessable Entity` - Invalid channel type or validation error
- `404 Not Found` - Event not found or inactive

**Note:** The API will return a `403` error if the admin has disabled `allow_override` in the notification settings.

## Analytics & Tracking

The package includes comprehensive analytics tracking for email notifications, allowing you to measure engagement and optimize your notification strategy.

### Email Open Tracking

When `track_opens` is enabled in analytics settings, a 1x1 transparent tracking pixel is automatically injected into all email notifications. When a user opens the email, the pixel loads and tracks the open event.

**Tracking Endpoint:**
```http
GET /notifier/track/open/{token}
```

This endpoint:
- Returns a 1x1 transparent PNG pixel
- Records the first open time in `opened_at`
- Increments the `opens_count` counter
- Respects analytics enabled/disabled settings

### Link Click Tracking

When `track_clicks` is enabled, all URLs in email content are automatically rewritten to use tracking URLs. When users click links, the system tracks the click and redirects to the original URL.

**Tracking Endpoint:**
```http
GET /notifier/track/click/{token}?url={encoded_url}
```

This endpoint:
- Records the first click time in `clicked_at`
- Increments the `clicks_count` counter
- Safely redirects to the original URL
- Validates URLs to prevent open redirect vulnerabilities

### Analytics Dashboard

Access the comprehensive analytics dashboard in your Filament admin panel:

**Location:** Navigate to **Notifier → Dashboard** in your Filament admin panel.

**Dashboard Features:**

1. **Overview Stats Widget**
   - Total notifications with trend chart
   - Success rate percentage
   - Pending notifications count
   - Failed notifications count
   - Active channels count

2. **Engagement Stats Widget**
   - Total opens
   - Open rate percentage
   - Total clicks
   - Click rate percentage
   - Click-through rate

3. **Time Series Chart**
   - 30-day line chart showing sent, opened, and clicked notifications
   - Full-width visualization
   - Auto-refreshes every 30 seconds

4. **Engagement Analytics Chart**
   - Combined bar and line chart
   - Shows opens and clicks
   - Overlays open rate % and click rate %
   - Dual Y-axis for counts and percentages
   - Last 7 days view

5. **Channel Performance Chart**
   - Bar chart comparing all active channels
   - Shows sent, opened, and clicked per channel
   - Helps identify best-performing channels

6. **Rate Limiting Status Widget**
   - Real-time usage for minute, hour, and day limits
   - Color-coded warnings
   - Percentage usage indicators
   - Auto-refreshes every 10 seconds

All widgets respect the analytics and rate limiting enabled/disabled settings.

### Data Retention

Configure data retention in the analytics settings. Use the cleanup command to remove old analytics data:

```bash
# Clean up analytics data older than retention period
php artisan notifier:cleanup-analytics

# Dry run to see what would be cleaned
php artisan notifier:cleanup-analytics --dry-run
```

The cleanup command:
- Respects the `retention_days` setting
- Anonymizes analytics data
- Can be scheduled via Laravel's task scheduler

## Rate Limiting

The package includes built-in rate limiting to prevent notification abuse and ensure system stability.

### Configuration

Configure rate limits in the Filament admin panel under **Notifier → Settings → Rate Limiting**:

- **Max Per Minute**: Maximum notifications allowed per minute (default: 60)
- **Max Per Hour**: Maximum notifications allowed per hour (default: 1000)
- **Max Per Day**: Maximum notifications allowed per day (default: 10000)

### How It Works

Rate limiting is enforced before notification creation:
- All three limits (minute, hour, day) are checked
- If any limit is exceeded, notification creation is blocked
- Rate limit violations are logged for monitoring
- Counters are tracked using Laravel Cache with appropriate TTL

### Monitoring

View real-time rate limiting status in the **Notifier Dashboard**:
- Current usage vs limits for each time period
- Color-coded warnings (red >90%, yellow >75%, green otherwise)
- Percentage usage indicators

## Configuration

### Channel Configuration

```php
// config/notifier.php
'channels' => [
    'email' => [
        'enabled' => true,
        'driver' => 'smtp',
        'from_address' => 'noreply@example.com',
        'from_name' => 'Your App',
    ],
    'slack' => [
        'enabled' => true,
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
        'channel' => '#notifications',
    ],
    'sms' => [
        'enabled' => true,
        'driver' => 'twilio',
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'phone_number' => env('TWILIO_PHONE_NUMBER'),
    ],
],
```

### Event Configuration

```php
'events' => [
    'user.registered' => [
        'channels' => ['email', 'slack'],
        'template' => 'welcome-email',
        'delay' => 0, // Send immediately
    ],
    'order.shipped' => [
        'channels' => ['email', 'sms'],
        'template' => 'order-shipped',
        'delay' => 300, // 5 minutes delay
    ],
],
```

## Database Tables

The package creates the following database tables with the `notifier_` prefix to avoid conflicts with Laravel's built-in tables:

- `notifier_channels` - Stores notification channel configurations
- `notifier_events` - Stores notification event definitions  
- `notifier_templates` - Stores notification templates
- `notifier_preferences` - Stores user notification preferences
- `notifier_notifications` - Stores sent notifications
- `notifier_settings` - Stores global notification settings

**Note:** These tables are separate from Laravel's built-in `notifications` table, which is used for database notifications. Our package provides a comprehensive notification management system that works alongside Laravel's native notification system.

### Publishing Migrations

To customize migrations (e.g., for UUID support, different indexes, or schema changes):

```bash
php artisan vendor:publish --tag=notifier-migrations
```

After publishing, edit the migration files in `database/migrations/` and run:

```bash
php artisan migrate
```

## API Reference

### NotifierManager

#### Methods

- `send($user, string $eventKey, array $data = [])`: Send a notification
- `sendNow($user, string $eventKey, array $data = [])`: Send immediately without queuing
- `sendToChannel($user, string $eventKey, string $channelType, array $data = [])`: Send to a specific channel
- `schedule($user, string $eventKey, Carbon $scheduledAt, array $data = [])`: Schedule a notification
- `registerChannel(string $type, $handler)`: Register a custom channel driver
- `registerEvent(string $key, array $config)`: Register an event configuration
- `getRegisteredChannels()`: Get all registered channel types
- `getRegisteredEvents()`: Get all registered event keys

### Available Facades

#### Preference Facade

```php
use Usamamuneerchaudhary\Notifier\Facades\Preference;

Preference::getUserPreferences($user, string $eventKey): array
Preference::getChannelsForEvent(NotificationEvent $event, ?NotificationPreference $preference): array
Preference::shouldSendToChannel($user, string $channelType, array $preferences): bool
```

#### Analytics Facade

```php
use Usamamuneerchaudhary\Notifier\Facades\Analytics;

Analytics::isEnabled(): bool
Analytics::isOpenTrackingEnabled(): bool
Analytics::isClickTrackingEnabled(): bool
Analytics::generateTrackingPixel(string $trackingToken): string
Analytics::trackOpen(Notification $notification): void
Analytics::trackClick(Notification $notification): void
```

#### UrlTracking Facade

```php
use Usamamuneerchaudhary\Notifier\Facades\UrlTracking;

UrlTracking::safeRedirect(string $url): RedirectResponse
UrlTracking::rewriteUrlsForTracking(string $content, string $token): string
```

#### NotificationRepo Facade

```php
use Usamamuneerchaudhary\Notifier\Facades\NotificationRepo;

NotificationRepo::findByToken(string $token): ?Notification
```

### Models

#### NotificationChannel
- `title`: Channel display name
- `type`: Channel type (email, sms, slack, etc.)
- `icon`: Icon for the channel
- `is_active`: Whether the channel is active
- `settings`: Channel-specific settings

#### NotificationTemplate
- `title`: Template display name
- `key`: Unique template identifier
- `type`: Template type
- `subject`: Email subject line
- `content`: Template content with variable placeholders
- `variables`: Available variables for the template

#### Notification
- `notification_template_id`: Associated template
- `user_id`: Target user
- `channel`: Channel type
- `subject`: Rendered subject
- `content`: Rendered content
- `status`: Notification status (pending, sent, failed)
- `scheduled_at`: Scheduled send time
- `sent_at`: Actual send time
- `opened_at`: First time notification was opened (analytics)
- `clicked_at`: First time a link was clicked (analytics)
- `opens_count`: Total number of opens (analytics)
- `clicks_count`: Total number of clicks (analytics)

## Testing

### Running Tests

```bash
composer test
```

The package includes comprehensive tests covering:
- Notification sending and scheduling
- User preferences and API endpoints
- Analytics tracking (opens, clicks)
- Rate limiting enforcement
- Email driver functionality
- Analytics cleanup command

### Test Coverage

**Feature Tests:**
- `NotifierManagerTest` - Core notification functionality
- `AnalyticsTrackingTest` - Email open and click tracking
- `RateLimitingTest` - Rate limit enforcement
- `AnalyticsCleanupCommandTest` - Data retention cleanup

**Unit Tests:**
- `RateLimitingServiceTest` - Rate limiting service logic
- `EmailDriverTest` - Email sending and tracking pixel injection

### Sending Test Notifications

You can send test notifications using the provided command:

```bash
# Send a test notification
php artisan notifier:test user.registered --user=1

# Send with custom data
php artisan notifier:test user.registered --user=1 --data="name=John Doe" --data="app_name=Test App"

# Send to specific channel
php artisan notifier:test user.registered --user=1 --channel=email
```

## Available Commands

- `php artisan notifier:install` - Install the package and create sample data
- `php artisan notifier:test {event} [options]` - Send test notifications
- `php artisan notifier:cleanup-analytics [--dry-run]` - Clean up old analytics data based on retention settings

### Cleanup Analytics Command

The cleanup command removes or anonymizes analytics data older than the configured retention period:

```bash
# Clean up old analytics data
php artisan notifier:cleanup-analytics

# Preview what would be cleaned (dry run)
php artisan notifier:cleanup-analytics --dry-run
```

**Options:**
- `--dry-run`: Show what would be cleaned without actually deleting data

**What it does:**
- Finds notifications with analytics data older than `retention_days`
- Anonymizes the data (sets opens/clicks to 0, clears timestamps)
- Preserves notification records for historical reference
- Respects the `analytics.enabled` setting

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Architecture & Design

The package follows Laravel best practices with a clean, service-oriented architecture:

- **Service Classes**: Regular classes that can be dependency injected or accessed via facades
- **Facades**: Convenient static access to services (registered in `composer.json`)
- **Dependency Injection**: Preferred approach for controllers and testable code
- **Service Container**: All services are registered as singletons for optimal performance

## Screenshots

### Dashboard Overview

<div align="center">
  <img src="public/images/screenshots/Screenshot 2025-12-19 at 23.28.25.png" alt="Dashboard Overview" width="100%">
</div>

### Analytics & Metrics

<table>
<tr>
<td width="50%">
  <img src="public/images/screenshots/Screenshot 2025-12-19 at 23.28.31.png" alt="Analytics Dashboard" width="100%">
</td>
<td width="50%">
  <img src="public/images/screenshots/Screenshot 2025-12-19 at 23.28.36.png" alt="Engagement Metrics" width="100%">
</td>
</tr>
</table>

### Notification Management

<table>
<tr>
<td width="50%">
  <img src="public/images/screenshots/Screenshot 2025-12-19 at 23.28.51.png" alt="Notification Channels" width="100%">
</td>
<td width="50%">
  <img src="public/images/screenshots/Screenshot 2025-12-19 at 23.28.59.png" alt="Notification Events" width="100%">
</td>
</tr>
</table>

### Templates & Settings

<table>
<tr>
<td width="50%">
  <img src="public/images/screenshots/Screenshot 2025-12-19 at 23.29.04.png" alt="Notification Templates" width="100%">
</td>
<td width="50%">
  <img src="public/images/screenshots/Screenshot 2025-12-19 at 23.29.12.png" alt="Notification Settings" width="100%">
</td>
</tr>
</table>

## Support

For support, please open an issue on GitHub or contact me at hello@usamamuneer.me.
