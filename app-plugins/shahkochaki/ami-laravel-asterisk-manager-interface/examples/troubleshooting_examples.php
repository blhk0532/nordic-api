<?php

/**
 * مثال حل مشکل "Target class [ami] does not exist"
 *
 * این فایل راه‌حل‌های مختلف برای حل این مشکل رایج را نمایش می‌دهد
 */

// ==============================================
// روش 1: استفاده مستقیم از Service Class
// ==============================================

use Shahkochaki\Ami\Services\AmiService;

// ایجاد instance با تنظیمات دستی
$ami = new AmiService([
    'host' => '192.168.1.100',
    'port' => 5038,
    'username' => 'admin',
    'secret' => 'mypass',
]);

// تست اتصال
try {
    $result = $ami->ping();
    echo '✅ اتصال موفق: '.json_encode($result)."\n";
} catch (Exception $e) {
    echo '❌ خطا در اتصال: '.$e->getMessage()."\n";
}

// ==============================================
// روش 2: استفاده از Service Container Laravel
// ==============================================

// در یک Controller یا Service Class
class ExampleController
{
    public function testConnection()
    {
        try {
            // روش اول: استفاده از app() helper
            $ami = app(AmiService::class);
            $result = $ami->ping();

            return response()->json([
                'status' => 'success',
                'message' => 'اتصال برقرار شد',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در اتصال: '.$e->getMessage(),
            ], 500);
        }
    }

    public function testWithResolve()
    {
        try {
            // روش دوم: استفاده از resolve() helper
            $ami = resolve(AmiService::class);
            $channels = $ami->getActiveChannels();

            return response()->json([
                'status' => 'success',
                'active_channels' => $channels,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

// ==============================================
// روش 3: Dependency Injection
// ==============================================

use Shahkochaki\Ami\Services\AmiService;

class CallController
{
    protected $ami;

    public function __construct(AmiService $ami)
    {
        $this->ami = $ami;
    }

    public function makeCall(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        try {
            $result = $this->ami->makeCall($from, $to);

            return response()->json([
                'status' => 'success',
                'message' => 'تماس برقرار شد',
                'call_id' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در برقراری تماس: '.$e->getMessage(),
            ], 500);
        }
    }
}

// ==============================================
// روش 4: استفاده از Facade (در صورت کار کردن)
// ==============================================

use Shahkochaki\Ami\Facades\Ami;

class SmsController
{
    public function sendSms(Request $request)
    {
        $number = $request->input('number');
        $message = $request->input('message');

        try {
            $result = Ami::sendSms($number, $message);

            return response()->json([
                'status' => 'success',
                'message' => 'پیام ارسال شد',
                'result' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در ارسال پیام: '.$e->getMessage(),
            ], 500);
        }
    }
}

// ==============================================
// روش 5: Service Provider دستی (اگر auto-discovery کار نکرد)
// ==============================================

// در فایل config/app.php در بخش providers:
/*
'providers' => [
    // ...
    Shahkochaki\Ami\Providers\AmiServiceProvider::class,
],
*/

// در بخش aliases (در صورت نیاز):
/*
'aliases' => [
    // ...
    'Ami' => Shahkochaki\Ami\Facades\Ami::class,
],
*/

// ==============================================
// روش 6: تست در Tinker
// ==============================================

/*
# در terminal:
php artisan tinker

# در tinker:
$ami = app(Shahkochaki\Ami\Services\AmiService::class);
$result = $ami->ping();
dd($result);

# یا:
$ami = new Shahkochaki\Ami\Services\AmiService([
    'host' => '192.168.1.100',
    'port' => 5038,
    'username' => 'admin',
    'secret' => 'mypass'
]);
$result = $ami->ping();
dd($result);
*/

// ==============================================
// روش 7: ایجاد Service Provider سفارشی
// ==============================================

use Illuminate\Support\ServiceProvider;
use Shahkochaki\Ami\Services\AmiService;

class CustomAmiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('ami', function ($app) {
            return new AmiService(config('ami', []));
        });

        $this->app->singleton(AmiService::class, function ($app) {
            return $app['ami'];
        });
    }

    public function boot()
    {
        // بروزرسانی config
        $this->mergeConfigFrom(__DIR__.'/../config/ami.php', 'ami');
    }
}

// سپس در config/app.php:
/*
'providers' => [
    // ...
    App\Providers\CustomAmiServiceProvider::class,
],
*/

// ==============================================
// روش 8: تست مستقل از Laravel
// ==============================================

require_once 'vendor/autoload.php';

use Shahkochaki\Ami\Services\AmiService;

$config = [
    'host' => '192.168.1.100',
    'port' => 5038,
    'username' => 'admin',
    'secret' => 'mypass',
];

$ami = new AmiService($config);

try {
    // تست ping
    echo "تست Ping...\n";
    $pingResult = $ami->ping();
    echo "✅ Ping موفق\n";

    // تست وضعیت
    echo "تست وضعیت سیستم...\n";
    $status = $ami->getStats();
    echo "✅ دریافت وضعیت موفق\n";
    print_r($status);
} catch (Exception $e) {
    echo '❌ خطا: '.$e->getMessage()."\n";
    echo "📝 بررسی کنید:\n";
    echo "  - آدرس و پورت سرور\n";
    echo "  - نام کاربری و رمز عبور AMI\n";
    echo "  - فعال بودن AMI در Asterisk\n";
    echo "  - دسترسی شبکه به سرور\n";
}

// ==============================================
// دستورات مفید برای عیب‌یابی
// ==============================================

/*
# پاک کردن cache‌ها:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# بازسازی autoload:
composer dump-autoload

# انتشار تنظیمات:
php artisan vendor:publish --tag=ami

# تست CLI:
php artisan ami:action Ping

# تست با تنظیمات سفارشی:
php artisan ami:action Ping --host=192.168.1.100 --port=5038 --username=admin --secret=mypass

# بررسی Service Provider:
php artisan route:list | grep ami
*/

echo "\n".str_repeat('=', 50)."\n";
echo "راهنمای حل مشکل Target class [ami] does not exist\n";
echo str_repeat('=', 50)."\n";
echo "1. استفاده مستقیم از AmiService\n";
echo "2. Dependency Injection در Controller\n";
echo "3. استفاده از app() یا resolve()\n";
echo "4. بررسی Service Provider\n";
echo "5. پاک کردن cache‌های Laravel\n";
echo "6. تست در php artisan tinker\n";
echo "7. مراجعه به docs/TROUBLESHOOTING.md\n";
echo str_repeat('=', 50)."\n";
