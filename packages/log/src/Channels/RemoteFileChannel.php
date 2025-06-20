<?php

namespace Packages\Log\Channels;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Packages\Log\Exceptions\LoggingException;

class RemoteFileChannel
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create a custom Monolog instance.
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('remote_file');
        
        try {
            $handler = $this->createHandler($config);
            $logger->pushHandler($handler);
        } catch (\Exception $e) {
            // Fall back to local logging if remote fails
            $this->logRemoteFailure($e);
            $handler = new StreamHandler(storage_path('logs/remote_fallback.log'), Logger::DEBUG);
            $logger->pushHandler($handler);
        }

        return $logger;
    }

    /**
     * Create the remote file handler
     */
    protected function createHandler(array $config): StreamHandler
    {
        $remotePath = $config['path'] ?? config('logging-package.remote_file.path');
        $level = $this->parseLevel($config['level'] ?? 'debug');

        // Ensure remote directory exists
        $this->ensureRemoteDirectoryExists($remotePath);

        // Create filename with date
        $filename = $remotePath . '/' . date('Y-m-d') . '_laravel.log';

        $handler = new StreamHandler($filename, $level);
        
        // Set formatter
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );
        
        $handler->setFormatter($formatter);

        return $handler;
    }

    /**
     * Ensure remote directory exists and is writable
     */
    protected function ensureRemoteDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new LoggingException("Could not create remote log directory: {$path}");
            }
        }

        if (!is_writable($path)) {
            throw new LoggingException("Remote log directory is not writable: {$path}");
        }
    }

    /**
     * Parse log level string to Monolog constant
     */
    protected function parseLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            'alert' => Logger::ALERT,
            'emergency' => Logger::EMERGENCY,
            default => Logger::DEBUG,
        };
    }

    /**
     * Log remote file failure to local logs
     */
    protected function logRemoteFailure(\Exception $e): void
    {
        $localHandler = new StreamHandler(storage_path('logs/remote_file_errors.log'), Logger::ERROR);
        $logger = new Logger('remote_file_error');
        $logger->pushHandler($localHandler);
        
        $logger->error('Remote file logging failed', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'remote_path' => $this->config['path'] ?? 'unknown',
        ]);
    }
}
