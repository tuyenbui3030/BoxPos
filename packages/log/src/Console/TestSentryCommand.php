<?php

namespace Packages\Log\Console;

use Illuminate\Console\Command;
use Packages\Log\Services\SentryService;
use Exception;

/**
 * Command to test Sentry integration
 * 
 * This command allows testing various Sentry features including
 * error reporting, message logging, and performance monitoring.
 */
class TestSentryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:test-sentry {--type=all : Type of test (all, error, message, performance)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Sentry integration and error reporting';

    protected SentryService $sentryService;

    /**
     * Create a new command instance.
     */
    public function __construct(SentryService $sentryService)
    {
        parent::__construct();
        $this->sentryService = $sentryService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('logging-package.sentry.enabled', false)) {
            $this->error('Sentry is not enabled. Please set SENTRY_LARAVEL_DSN in your .env file.');
            return 1;
        }

        $testType = $this->option('type');

        $this->info('Testing Sentry integration...');
        $this->newLine();

        switch ($testType) {
            case 'error':
                $this->testErrorReporting();
                break;
            case 'message':
                $this->testMessageLogging();
                break;
            case 'performance':
                $this->testPerformanceMonitoring();
                break;
            case 'all':
            default:
                $this->testErrorReporting();
                $this->testMessageLogging();
                $this->testPerformanceMonitoring();
                break;
        }

        $this->newLine();
        $this->info('✅ Sentry testing completed! Check your Sentry dashboard for the test events.');
        
        return 0;
    }

    /**
     * Test error reporting to Sentry
     */
    protected function testErrorReporting(): void
    {
        $this->info('🔥 Testing error reporting...');

        try {
            // Create a test exception
            throw new Exception('This is a test exception from BoxPos Log Package');
        } catch (Exception $e) {
            $eventId = $this->sentryService->reportException($e, [
                'test_context' => 'Sentry integration test',
                'command' => 'log:test-sentry',
                'timestamp' => now()->toISOString(),
            ], [
                'test_type' => 'error_reporting',
                'environment' => app()->environment(),
            ]);

            if ($eventId) {
                $this->line("   ✅ Exception reported with ID: {$eventId}");
            } else {
                $this->line("   ❌ Failed to report exception");
            }
        }
    }

    /**
     * Test message logging to Sentry
     */
    protected function testMessageLogging(): void
    {
        $this->info('📝 Testing message logging...');

        $messages = [
            ['message' => 'Test info message from BoxPos', 'level' => 'info'],
            ['message' => 'Test warning message from BoxPos', 'level' => 'warning'],
            ['message' => 'Test error message from BoxPos', 'level' => 'error'],
        ];

        foreach ($messages as $messageData) {
            $eventId = $this->sentryService->reportMessage(
                $messageData['message'],
                $messageData['level'],
                [
                    'test_context' => 'Message logging test',
                    'command' => 'log:test-sentry',
                    'timestamp' => now()->toISOString(),
                ],
                [
                    'test_type' => 'message_logging',
                    'message_level' => $messageData['level'],
                ]
            );

            if ($eventId) {
                $this->line("   ✅ {$messageData['level']} message logged with ID: {$eventId}");
            } else {
                $this->line("   ❌ Failed to log {$messageData['level']} message");
            }
        }
    }

    /**
     * Test performance monitoring
     */
    protected function testPerformanceMonitoring(): void
    {
        $this->info('⚡ Testing performance monitoring...');

        // Start a test transaction
        $transaction = $this->sentryService->startTransaction(
            'test-command-performance',
            'command.execution',
            [
                'command' => 'log:test-sentry',
                'test_type' => 'performance',
            ]
        );

        if ($transaction) {
            $this->line('   ✅ Performance transaction started');

            // Add some breadcrumbs
            $this->sentryService->addBreadcrumb(
                'Starting performance test',
                'test',
                'info',
                ['step' => 'initialization']
            );

            // Simulate some work
            $this->simulateWork();

            $this->sentryService->addBreadcrumb(
                'Work simulation completed',
                'test',
                'info',
                ['step' => 'completion']
            );

            // Log performance data
            $this->sentryService->logPerformance([
                'execution_time_ms' => 150.5,
                'memory_used_mb' => 12.5,
                'peak_memory_mb' => 15.2,
                'test_metric' => 'performance_test',
            ]);

            // Finish the transaction
            $transaction->finish();
            $this->line('   ✅ Performance transaction completed');
        } else {
            $this->line('   ❌ Failed to start performance transaction');
        }
    }

    /**
     * Simulate some work for performance testing
     */
    protected function simulateWork(): void
    {
        // Simulate database queries
        $this->sentryService->addBreadcrumb(
            'Simulating database queries',
            'database',
            'info'
        );

        // Simulate some processing time
        usleep(100000); // 100ms

        // Simulate cache operations
        $this->sentryService->addBreadcrumb(
            'Simulating cache operations',
            'cache',
            'info'
        );

        usleep(50000); // 50ms
    }
}
