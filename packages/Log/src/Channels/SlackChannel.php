<?php

namespace Packages\Log\Channels;

use Monolog\Logger;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Formatter\SlackLineFormatter;
use Packages\Log\Exceptions\LoggingException;

class SlackChannel
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
        $logger = new Logger('slack');
        
        $webhookUrl = $config['webhook'] ?? config('logging-package.slack.webhook_url');
        
        if (!$webhookUrl) {
            throw new LoggingException('Slack webhook URL is required for Slack logging channel');
        }

        try {
            $handler = $this->createSlackHandler($config, $webhookUrl);
            $logger->pushHandler($handler);
        } catch (\Exception $e) {
            // Log to file if Slack fails
            $this->logSlackFailure($e);
            throw new LoggingException('Failed to create Slack logging handler: ' . $e->getMessage());
        }

        return $logger;
    }

    /**
     * Create Slack webhook handler
     */
    protected function createSlackHandler(array $config, string $webhookUrl): SlackWebhookHandler
    {
        $level = $this->parseLevel($config['level'] ?? 'critical');
        $channel = config('logging-package.slack.channel', '#alerts');
        $username = config('logging-package.slack.username', 'BoxPos Logger');
        $iconEmoji = config('logging-package.slack.icon_emoji', ':warning:');

        $handler = new SlackWebhookHandler(
            $webhookUrl,
            $channel,
            $username,
            true, // useAttachment
            $iconEmoji,
            false, // useShortAttachment
            true, // includeContextAndExtra
            $level
        );

        // Set custom formatter
        $formatter = new SlackLineFormatter();
        $handler->setFormatter($formatter);

        return $handler;
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
            default => Logger::CRITICAL,
        };
    }

    /**
     * Log Slack failure to local logs
     */
    protected function logSlackFailure(\Exception $e): void
    {
        $localPath = storage_path('logs/slack_errors.log');
        
        $errorMessage = sprintf(
            "[%s] Slack logging failed: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        file_put_contents($localPath, $errorMessage, FILE_APPEND | LOCK_EX);
    }
}
