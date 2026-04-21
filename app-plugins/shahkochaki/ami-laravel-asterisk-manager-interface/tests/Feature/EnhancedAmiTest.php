<?php

namespace Shahkochaki\Ami\Tests\Feature;

use React\EventLoop\LoopInterface;
use React\SocketClient\ConnectorInterface;
use Shahkochaki\Ami\Factory;
use Shahkochaki\Ami\Tests\TestCase;

class EnhancedAmiTest extends TestCase
{
    /**
     * Test basic AMI connection
     */
    public function test_ami_connection()
    {
        $this->artisan('ami:action', [
            'action' => 'Ping',
            '--host' => 'localhost',
            '--port' => 5038,
            '--username' => 'test',
            '--secret' => 'test',
        ])->assertExitCode(0);
    }

    /**
     * Test call management
     */
    public function test_call_management()
    {
        // Test call origination
        $this->artisan('ami:action', [
            'action' => 'Originate',
            '--arguments' => [
                'Channel' => 'SIP/1001',
                'Context' => 'default',
                'Exten' => '1002',
                'Priority' => '1',
            ],
        ])->assertExitCode(0);

        // Test channel status
        $this->artisan('ami:action', [
            'action' => 'Status',
        ])->assertExitCode(0);
    }

    /**
     * Test SMS functionality
     */
    public function test_sms_management()
    {
        // Test single SMS
        $this->artisan('ami:dongle:sms', [
            'number' => '09123456789',
            'message' => 'Test message',
            'device' => 'dongle0',
        ])->assertExitCode(0);

        // Test PDU mode
        $longMessage = str_repeat('This is a long message. ', 10);
        $this->artisan('ami:dongle:sms', [
            'number' => '09123456789',
            'message' => $longMessage,
            'device' => 'dongle0',
            '--pdu' => true,
        ])->assertExitCode(0);
    }

    /**
     * Test USSD functionality
     */
    public function test_ussd_management()
    {
        $this->artisan('ami:dongle:ussd', [
            'device' => 'dongle0',
            'ussd' => '*141#',
        ])->assertExitCode(0);
    }

    /**
     * Test event listening
     */
    public function test_event_listening()
    {
        // This would be more complex in a real test
        $this->artisan('ami:listen', [
            '--timeout' => 5, // Short timeout for testing
        ])->assertExitCode(0);
    }

    /**
     * Test CLI interface
     */
    public function test_cli_interface()
    {
        $this->artisan('ami:cli', [
            'command' => 'core show channels',
            '--autoclose' => true,
        ])->assertExitCode(0);
    }

    /**
     * Test error handling
     */
    public function test_error_handling()
    {
        // Test with invalid credentials
        $this->artisan('ami:action', [
            'action' => 'Ping',
            '--host' => 'localhost',
            '--port' => 5038,
            '--username' => 'invalid',
            '--secret' => 'invalid',
        ])->assertExitCode(1);

        // Test with invalid device
        $this->artisan('ami:dongle:sms', [
            'number' => '09123456789',
            'message' => 'Test',
            'device' => 'invalid_device',
        ])->assertExitCode(1);
    }

    /**
     * Test configuration loading
     */
    public function test_configuration_loading()
    {
        $config = config('ami');

        $this->assertArrayHasKey('host', $config);
        $this->assertArrayHasKey('port', $config);
        $this->assertArrayHasKey('dongle', $config);
        $this->assertArrayHasKey('events', $config);
    }

    /**
     * Test service providers
     */
    public function test_service_providers()
    {
        $this->assertTrue(
            app()->bound('command.ami.listen')
        );

        $this->assertTrue(
            app()->bound('command.ami.action')
        );

        $this->assertTrue(
            app()->bound('command.ami.dongle.sms')
        );

        $this->assertTrue(
            app()->bound('command.ami.dongle.ussd')
        );
    }

    /**
     * Test factory creation
     */
    public function test_factory_creation()
    {
        $factory = app('ami.factory');
        $this->assertInstanceOf(Factory::class, $factory);
    }

    /**
     * Test event loop
     */
    public function test_event_loop()
    {
        $loop = app('ami.event_loop');
        $this->assertInstanceOf(LoopInterface::class, $loop);
    }

    /**
     * Test connector
     */
    public function test_connector()
    {
        $connector = app('ami.connector');
        $this->assertInstanceOf(ConnectorInterface::class, $connector);
    }
}

/**
 * Performance Tests
 */
class PerformanceTest extends TestCase
{
    /**
     * Test bulk SMS performance
     */
    public function test_bulk_sms_performance()
    {
        $recipients = [];
        for ($i = 0; $i < 10; $i++) {
            $recipients[] = '0912345678'.$i;
        }

        $startTime = microtime(true);

        // This would normally use the BulkSmsService
        foreach ($recipients as $recipient) {
            $this->artisan('ami:dongle:sms', [
                'number' => $recipient,
                'message' => 'Performance test message',
                'device' => 'dongle0',
            ]);
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Assert that it completes within reasonable time
        $this->assertLessThan(60, $duration, 'Bulk SMS should complete within 60 seconds');
    }

    /**
     * Test connection pooling performance
     */
    public function test_connection_pooling_performance()
    {
        $startTime = microtime(true);

        // Multiple quick requests
        for ($i = 0; $i < 5; $i++) {
            $this->artisan('ami:action', [
                'action' => 'Ping',
            ]);
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // With connection pooling, this should be faster
        $this->assertLessThan(10, $duration, 'Multiple requests should complete quickly with pooling');
    }
}

/**
 * Integration Tests
 */
class IntegrationTest extends TestCase
{
    /**
     * Test full call workflow
     */
    public function test_full_call_workflow()
    {
        // Originate call
        $this->artisan('ami:action', [
            'action' => 'Originate',
            '--arguments' => [
                'Channel' => 'SIP/1001',
                'Context' => 'default',
                'Exten' => '1002',
                'Priority' => '1',
            ],
        ])->assertExitCode(0);

        // Check status
        $this->artisan('ami:action', [
            'action' => 'Status',
        ])->assertExitCode(0);

        // Hangup (would need channel info in real test)
        $this->artisan('ami:action', [
            'action' => 'Hangup',
            '--arguments' => [
                'Channel' => 'SIP/1001-00000001',
            ],
        ])->assertExitCode(0);
    }

    /**
     * Test SMS with delivery confirmation
     */
    public function test_sms_with_delivery_confirmation()
    {
        // Send SMS
        $result = $this->artisan('ami:dongle:sms', [
            'number' => '09123456789',
            'message' => 'Test message with confirmation',
            'device' => 'dongle0',
        ]);

        $this->assertEquals(0, $result);

        // Check delivery status (would need message ID in real implementation)
        $this->artisan('ami:action', [
            'action' => 'DongleSMSStatus',
            '--arguments' => [
                'Device' => 'dongle0',
            ],
        ])->assertExitCode(0);
    }
}
