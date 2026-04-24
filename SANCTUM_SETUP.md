# Sanctum API Token Authentication Setup

Complete guide for implementing Sanctum token-based authentication on other Laravel instances.

## Overview

This setup allows you to issue API tokens to users that can be used to authenticate requests to your API from external applications, mobile apps, or other services. The tokens are stateless and don't require session management.

## Quick Start

### 1. Install Sanctum (if not already installed)

```bash
composer require laravel/sanctum

# If using Laravel < 13, run the install command
php artisan install:api
```

### 2. Add HasApiTokens Trait to User Model

Update your `User` model at `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Add HasApiTokens
    
    // ... rest of your model
}
```

### 3. Copy the Implementation Files

Copy these files from your primary instance to other instances:

```
FROM: /home/baba/apps/api/app/Http/Controllers/Api/SanctumAuthController.php
TO:   [NEW_PROJECT]/app/Http/Controllers/Api/SanctumAuthController.php

FROM: /home/baba/apps/api/app/Http/Requests/Api/SanctumTokenRequest.php
TO:   [NEW_PROJECT]/app/Http/Requests/Api/SanctumTokenRequest.php
```

### 4. Register Routes

Update your `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\SanctumAuthController;

// Sanctum authentication (public routes)
Route::post('/sanctum/token', [SanctumAuthController::class, 'issueToken'])
    ->throttle('15,1')
    ->name('sanctum.token');

// Protected Sanctum routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [SanctumAuthController::class, 'user']);
    Route::get('/sanctum/tokens', [SanctumAuthController::class, 'listTokens']);
    Route::post('/sanctum/revoke', [SanctumAuthController::class, 'revokeToken']);
    Route::delete('/sanctum/tokens/{tokenId}', [SanctumAuthController::class, 'revokeTokenById']);
});

// Your other protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    // ... more routes
});
```

### 5. Run Migrations

```bash
php artisan migrate
```

This creates the `personal_access_tokens` table.

---

## API Endpoints

### 1. Issue Token (Login)

**Endpoint:** `POST /api/sanctum/token`

**Request:**
```bash
curl -X POST http://localhost:8000/api/sanctum/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "device_name": "iPhone 15"
  }'
```

**Response (201):**
```json
{
  "message": "Token created successfully",
  "token": "1|wN2qKTLkwQqLDRz5H5nJ3w5mM5X5n5X5",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

**Error Response (422):**
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

### 2. Get Current User

**Endpoint:** `GET /api/user`

**Headers:**
```
Authorization: Bearer 1|wN2qKTLkwQqLDRz5H5nJ3w5mM5X5n5X5
```

**Request:**
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|wN2qKTLkwQqLDRz5H5nJ3w5mM5X5n5X5"
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  },
  "abilities": ["*"]
}
```

---

### 3. List All Tokens

**Endpoint:** `GET /api/sanctum/tokens`

**Request:**
```bash
curl -X GET http://localhost:8000/api/sanctum/tokens \
  -H "Authorization: Bearer 1|wN2qKTLkwQqLDRz5H5nJ3w5mM5X5n5X5"
```

**Response (200):**
```json
{
  "tokens": [
    {
      "id": 1,
      "name": "iPhone 15",
      "created_at": "2025-04-24T10:30:00Z",
      "last_used_at": "2025-04-24T11:00:00Z"
    },
    {
      "id": 2,
      "name": "Chrome Desktop",
      "created_at": "2025-04-24T09:15:00Z",
      "last_used_at": "2025-04-24T10:45:00Z"
    }
  ]
}
```

---

### 4. Revoke Current Token

**Endpoint:** `POST /api/sanctum/revoke`

**Request:**
```bash
curl -X POST http://localhost:8000/api/sanctum/revoke \
  -H "Authorization: Bearer 1|wN2qKTLkwQqLDRz5H5nJ3w5mM5X5n5X5"
```

**Response (200):**
```json
{
  "message": "Token revoked successfully"
}
```

---

### 5. Revoke Specific Token

**Endpoint:** `DELETE /api/sanctum/tokens/{tokenId}`

**Request:**
```bash
curl -X DELETE http://localhost:8000/api/sanctum/tokens/1 \
  -H "Authorization: Bearer 1|wN2qKTLkwQqLDRz5H5nJ3w5mM5X5n5X5"
```

**Response (200):**
```json
{
  "message": "Token revoked successfully"
}
```

---

## Using Tokens in Your Application

### JavaScript / Fetch API

```javascript
// 1. Get a token (login)
const response = await fetch('https://api.example.com/api/sanctum/token', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123',
    device_name: 'Web Browser'
  })
});

const data = await response.json();
const token = data.token;

// Save token to localStorage
localStorage.setItem('api_token', token);

// 2. Use token in subsequent requests
const userResponse = await fetch('https://api.example.com/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const user = await userResponse.json();
console.log(user);
```

### Python / Requests

```python
import requests

# 1. Get token
response = requests.post('https://api.example.com/api/sanctum/token', json={
    'email': 'user@example.com',
    'password': 'password123',
    'device_name': 'Python Script'
})

token = response.json()['token']

# 2. Use token in requests
headers = {'Authorization': f'Bearer {token}'}
user = requests.get('https://api.example.com/api/user', headers=headers).json()
print(user)
```

### PHP / GuzzleHTTP

```php
<?php

use GuzzleHttp\Client;

$client = new Client();

// 1. Get token
$response = $client->post('https://api.example.com/api/sanctum/token', [
    'json' => [
        'email' => 'user@example.com',
        'password' => 'password123',
        'device_name' => 'PHP Client'
    ]
]);

$data = json_decode($response->getBody(), true);
$token = $data['token'];

// 2. Use token in requests
$userResponse = $client->get('https://api.example.com/api/user', [
    'headers' => ['Authorization' => "Bearer $token"]
]);

$user = json_decode($userResponse->getBody(), true);
print_r($user);
```

---

## Configuring Token Expiration

Edit `config/sanctum.php` (or create if doesn't exist):

```php
<?php

return [
    // Expire tokens after 1 year (525600 minutes)
    'expiration' => 525600,

    // Or don't expire (null = never expire)
    'expiration' => null,
];
```

To specify expiration per token:

```php
// Expire in 1 week
$user->createToken('device-name', ['*'], now()->addWeeks(1))->plainTextToken;

// Expire in 24 hours
$user->createToken('device-name', ['*'], now()->addDay())->plainTextToken;
```

---

## Token Abilities (Scopes)

Restrict what a token can do:

### Issue Token with Specific Abilities

```php
// Token can only read data
$user->createToken('read-only-device', ['read'])->plainTextToken;

// Token can read and write
$user->createToken('full-access', ['read', 'write'])->plainTextToken;

// Token can do anything
$user->createToken('admin-device', ['*'])->plainTextToken;
```

### Protect Routes by Ability

```php
// Only allow if token has 'write' ability
Route::post('/posts', [PostController::class, 'store'])
    ->middleware('auth:sanctum', 'ability:write');

// Only allow if token has 'delete' ability
Route::delete('/posts/{post}', [PostController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:delete');
```

### Check Ability in Code

```php
if ($request->user()->tokenCan('write')) {
    // Allow write operations
}

if ($request->user()->tokenCan('delete')) {
    // Allow delete operations
}
```

---

## Testing

### Pest Test Example

```php
<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user can get token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/sanctum/token', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'device_name' => 'Test Device',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'user', 'message']);
});

test('authenticated user can access protected route', function () {
    $user = User::factory()->create();
    
    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user');

    $response->assertOk()
        ->assertJsonPath('user.id', $user->id);
});

test('invalid credentials return error', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/sanctum/token', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
        'device_name' => 'Test Device',
    ]);

    $response->assertUnauthorized();
});
```

---

## Security Best Practices

1. **Always use HTTPS** - Never transmit tokens over plain HTTP
2. **Store tokens securely** - Use secure storage (localStorage with HttpOnly flag, secure storage in mobile apps)
3. **Set token expiration** - Set reasonable expiration times in config
4. **Revoke tokens regularly** - Implement token rotation
5. **Don't log tokens** - Don't log or display plain tokens
6. **Rate limiting** - Token endpoint is rate-limited to 15 requests per minute
7. **Validate input** - Use FormRequest validation (SanctumTokenRequest)
8. **Monitor token usage** - Check `last_used_at` field to detect suspicious activity

---

## Troubleshooting

### "Token not found" when authenticating

- Make sure the token is passed in the `Authorization: Bearer` header
- Check that the token hasn't been revoked
- Verify the token hasn't expired (if expiration is configured)

### "The provided credentials are incorrect"

- Double-check email and password are correct
- Verify the user account exists in the database
- Check that the password was properly hashed when user was created

### "Middleware 'auth:sanctum' not found"

- Run migrations: `php artisan migrate`
- Make sure `config/sanctum.php` exists (or run `php artisan install:api`)

### Token works in one endpoint but not another

- Verify the route is protected with `middleware('auth:sanctum')`
- Check if the route has ability restrictions: `middleware('auth:sanctum', 'ability:read')`
- Ensure token was issued with the required ability

---

## Configuration Reference

**File:** `config/sanctum.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. This includes SPA applications that execute
    | requests via the browser.
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,127.0.0.1:3000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | API Token Expiration
    |--------------------------------------------------------------------------
    | This value controls the number of minutes until an issued token will
    | be considered expired. If this is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party tokens.
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | API Token Prefix
    |--------------------------------------------------------------------------
    | API tokens may have a prefix that helps identify their purpose, such as
    | distinguishing between personal access tokens and oauth tokens. This
    | won't be shown to users, but you may use this for internal tracking.
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    | When authenticating your first-party SPA or mobile application, Sanctum
    | requires a middleware to ensure your requests are coming from Client-side
    | CSRF protection.
    */

    'middleware' => [
        'verify_csrf_token' => \App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => \App\Http\Middleware\EncryptCookies::class,
    ],
];
```

---

## Complete Setup Checklist

- [ ] Sanctum installed via `composer require laravel/sanctum`
- [ ] `HasApiTokens` trait added to User model
- [ ] `SanctumAuthController` copied to `app/Http/Controllers/Api/`
- [ ] `SanctumTokenRequest` copied to `app/Http/Requests/Api/`
- [ ] Routes added to `routes/api.php`
- [ ] Database migrations run: `php artisan migrate`
- [ ] Token endpoints tested with curl/Postman
- [ ] Token expiration configured in `config/sanctum.php` (optional)
- [ ] Tests written and passing
- [ ] Documentation updated for your team
- [ ] Environment variables configured (if needed)

---

## Support

For more information, refer to the official Laravel Sanctum documentation:
https://laravel.com/docs/sanctum

