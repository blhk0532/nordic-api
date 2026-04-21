<?php

use Shahkochaki\Ami\Services\AmiService;
use Shahkochaki\Ami\Services\SystemManager;

/**
 * تست سریع برای بررسی حل شدن مشکل "Target class [ami] does not exist"
 */
echo "=== تست حل مشکل Target class [ami] does not exist ===\n\n";

// تست 1: بررسی وجود کلاس‌های اصلی
echo "1. بررسی وجود کلاس‌ها:\n";

$classes = [
    'Shahkochaki\Ami\Services\AmiService',
    'Shahkochaki\Ami\Services\SystemManager',
    'Shahkochaki\Ami\Providers\AmiServiceProvider',
    'Shahkochaki\Ami\Facades\Ami',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   ✅ $class - موجود\n";
    } else {
        echo "   ❌ $class - وجود ندارد\n";
    }
}

echo "\n2. تست ایجاد AmiService:\n";

try {
    $config = [
        'host' => '127.0.0.1',
        'port' => 5038,
        'username' => 'test',
        'secret' => 'test',
    ];

    $ami = new AmiService($config);
    echo "   ✅ AmiService با موفقیت ایجاد شد\n";

    // تست متدهای موجود
    $methods = ['ping', 'getStats', 'system', 'calls', 'sms'];
    foreach ($methods as $method) {
        if (method_exists($ami, $method)) {
            echo "   ✅ متد $method موجود است\n";
        } else {
            echo "   ❌ متد $method موجود نیست\n";
        }
    }
} catch (Exception $e) {
    echo '   ❌ خطا در ایجاد AmiService: '.$e->getMessage()."\n";
}

echo "\n3. تست SystemManager:\n";

try {
    $systemManager = new SystemManager($config);
    echo "   ✅ SystemManager با موفقیت ایجاد شد\n";

    // تست متدهای موجود
    $methods = ['shutdownServer', 'restartServer', 'getServerStatus', 'reloadConfiguration'];
    foreach ($methods as $method) {
        if (method_exists($systemManager, $method)) {
            echo "   ✅ متد $method موجود است\n";
        } else {
            echo "   ❌ متد $method موجود نیست\n";
        }
    }
} catch (Exception $e) {
    echo '   ❌ خطا در ایجاد SystemManager: '.$e->getMessage()."\n";
}

echo "\n4. راهنمای استفاده:\n";
echo "   📖 برای استفاده در Laravel:\n";
echo "   \$ami = new \\Shahkochaki\\Ami\\Services\\AmiService(\$config);\n";
echo "   \$result = \$ami->ping();\n\n";

echo "   📖 برای مدیریت سیستم:\n";
echo "   \$systemManager = new \\Shahkochaki\\Ami\\Services\\SystemManager(\$config);\n";
echo "   \$systemManager->shutdownServer(true, 'Maintenance');\n\n";

echo "   📖 در صورت مشکل، مراجعه کنید به:\n";
echo "   - docs/TROUBLESHOOTING.md\n";
echo "   - examples/troubleshooting_examples.php\n\n";

echo "=== پایان تست ===\n";

/**
 * تست اتصال به سرور (در صورت در دسترس بودن)
 */
function testConnection($host = '127.0.0.1', $port = 5038, $username = 'admin', $secret = 'amp111')
{
    echo "\n=== تست اتصال به سرور AMI ===\n";

    try {
        $ami = new AmiService([
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'secret' => $secret,
        ]);

        echo "Service ایجاد شد، تست اتصال...\n";

        // در اینجا تست واقعی اتصال انجام می‌شود
        // فعلاً فقط بررسی می‌کنیم که Service درست کار می‌کند

        echo "✅ AMI Service آماده استفاده است\n";
        echo "💡 برای تست اتصال واقعی، دستور زیر را اجرا کنید:\n";
        echo "php artisan ami:action Ping --host=$host --port=$port --username=$username --secret=$secret\n";
    } catch (Exception $e) {
        echo '❌ خطا: '.$e->getMessage()."\n";
    }
}

// اگر پارامترهای سرور را دارید، می‌توانید تست کنید:
// testConnection('192.168.1.100', 5038, 'admin', 'mypass');
