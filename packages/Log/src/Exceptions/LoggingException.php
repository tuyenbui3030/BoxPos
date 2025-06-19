<?php

namespace Packages\Log\Exceptions;

use Exception;

class LoggingException extends Exception
{
    /**
     * Create a new logging exception instance.
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for configuration errors
     */
    public static function configurationError(string $message): self
    {
        return new self("Configuration Error: {$message}");
    }

    /**
     * Create exception for file system errors
     */
    public static function fileSystemError(string $message, string $path): self
    {
        return new self("File System Error: {$message} (Path: {$path})");
    }

    /**
     * Create exception for remote logging errors
     */
    public static function remoteLoggingError(string $message, string $service): self
    {
        return new self("Remote Logging Error ({$service}): {$message}");
    }

    /**
     * Create exception for permission errors
     */
    public static function permissionError(string $path): self
    {
        return new self("Permission Error: Unable to write to log directory or file (Path: {$path})");
    }

    /**
     * Create exception for missing dependencies
     */
    public static function missingDependency(string $dependency): self
    {
        return new self("Missing Dependency: {$dependency} is required for logging functionality");
    }

    /**
     * Create exception for disk space errors
     */
    public static function diskSpaceError(string $path): self
    {
        return new self("Disk Space Error: Insufficient disk space for logging (Path: {$path})");
    }
}
