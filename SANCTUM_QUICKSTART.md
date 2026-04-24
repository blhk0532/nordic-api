# Sanctum Implementation Quick Reference

Quick copy-paste guide for adding Sanctum to other Laravel instances.

## 3-Minute Setup

### Step 1: Install Sanctum
```bash
composer require laravel/sanctum
```

### Step 2: Add to User Model
```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
```

### Step 3: Copy Files
```bash
# Copy from primary instance
cp /home/baba/apps/api/app/Http/Controllers/Api/SanctumAuthController.php \
   [YOUR_PROJECT]/app/Http/Controllers/Api/

cp /home/baba/apps/api/app/Http/Requests/Api/SanctumTokenRequest.php \
   [YOUR_PROJECT]/app/Http/Requests/Api/
```

### Step 4: Update Routes (routes/api.php)
```php
<?php

use App\Http\Controllers\Api\SanctumAuthController;

// Public: Issue token
Route::post('/sanctum/token', [SanctumAuthController::class, 'issueToken'])
    ->throttle('15,1');

// Protected: Token routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [SanctumAuthController::class, 'user']);
    Route::get('/sanctum/tokens', [SanctumAuthController::class, 'listTokens']);
    Route::post('/sanctum/revoke', [SanctumAuthController::class, 'revokeToken']);
    Route::delete('/sanctum/tokens/{tokenId}', [SanctumAuthController::class, 'revokeTokenById']);
});

// Your protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    // ... more routes
});
```

### Step 5: Migrate & Test
```bash
php artisan migrate
php artisan test tests/Feature/SanctumAuthenticationTest.php
```

**Done!** 🎉

---

## Common Curl Examples

### Get Token
```bash
curl -X POST http://localhost:8000/api/sanctum/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "device_name": "My App"
  }'
```

### Use Token
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/user
```

### List Tokens
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/sanctum/tokens
```

### Revoke Token
```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/sanctum/revoke
```

---

## JavaScript Quick Start

```javascript
// Login and store token
async function login() {
  const res = await fetch('/api/sanctum/token', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      email: 'user@example.com',
      password: 'password123',
      device_name: 'Web'
    })
  });
  
  const { token } = await res.json();
  localStorage.setItem('token', token);
  return token;
}

// Use token in requests
async function getUser(token) {
  const res = await fetch('/api/user', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return res.json();
}

// Example usage
const token = await login();
const user = await getUser(token);
console.log(user);
```

---

## File Locations

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/SanctumAuthController.php` | Token management endpoints |
| `app/Http/Requests/Api/SanctumTokenRequest.php` | Request validation |
| `routes/api.php` | API routes (update with token routes) |
| `config/sanctum.php` | Optional configuration |
| `tests/Feature/SanctumAuthenticationTest.php` | Test suite |

---

## Customization

### Change Token Expiration
Edit `config/sanctum.php`:
```php
'expiration' => 525600, // minutes (1 year)
```

### Per-Token Expiration
```php
$user->createToken(
    'device-name',
    ['*'],
    now()->addDays(7) // Expires in 7 days
)->plainTextToken;
```

### Token Abilities (Scopes)
```php
// Issue token with read-only access
$user->createToken('read-only', ['read'])->plainTextToken;

// Protect route by ability
Route::post('/posts', [PostController::class, 'store'])
    ->middleware('auth:sanctum', 'ability:write');
```

---

## Environment Configuration

Add to `.env` if customizing:
```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,example.com
SANCTUM_TOKEN_PREFIX=api
```

---

## Testing with Pest

```php
test('get token with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/sanctum/token', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'device_name' => 'Test',
    ]);

    $response->assertCreated();
    expect($response->json('token'))->not->toBeEmpty();
});

test('access protected route with token', function () {
    $user = User::factory()->create();
    
    Sanctum::actingAs($user, ['*']);
    
    $response = $this->getJson('/api/user');
    
    $response->assertOk();
});
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Middleware 'auth:sanctum' not found" | Run `php artisan migrate` |
| Token not working | Check it's in `Authorization: Bearer` header |
| "Credentials incorrect" | Verify email/password are correct |
| Token keeps expiring | Check `config/sanctum.php` expiration value |
| Can't create tokens | Ensure `HasApiTokens` trait on User model |

---

## Full Documentation

See `SANCTUM_SETUP.md` for complete guide with detailed examples and best practices.

For Laravel docs: https://laravel.com/docs/sanctum

