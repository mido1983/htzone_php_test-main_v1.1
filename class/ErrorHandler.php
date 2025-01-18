<?php

class ErrorHandler {
    private $logger;

    public function __construct() {
        $this->logger = Logger::getInstance();
        $this->registerHandlers();
    }

    private function registerHandlers() {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $this->logger->error("PHP Error: $errstr", [
            'errno' => $errno,
            'file' => $errfile,
            'line' => $errline
        ]);

        if (getenv('APP_ENV') === 'production') {
            $this->displayProductionError();
        } else {
            $this->displayDevelopmentError($errno, $errstr, $errfile, $errline);
        }

        return true;
    }

    public function handleException($exception) {
        $this->logger->error("Uncaught Exception: " . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        if (getenv('APP_ENV') === 'production') {
            $this->displayProductionError();
        } else {
            $this->displayDevelopmentError(
                E_ERROR,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            );
        }
    }

    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->logger->error("Fatal Error: " . $error['message'], [
                'file' => $error['file'],
                'line' => $error['line']
            ]);

            if (getenv('APP_ENV') === 'production') {
                $this->displayProductionError();
            } else {
                $this->displayDevelopmentError(
                    $error['type'],
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
            }
        }
    }

    private function displayProductionError() {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        include __DIR__ . '/../500.html';
        exit(1);
    }

    private function displayDevelopmentError($errno, $errstr, $errfile, $errline, $trace = '') {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        echo "<h1>Error</h1>";
        echo "<p><strong>Type:</strong> " . $this->getErrorType($errno) . "</p>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($errstr) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($errfile) . "</p>";
        echo "<p><strong>Line:</strong> " . $errline . "</p>";
        if ($trace) {
            echo "<h2>Stack Trace</h2>";
            echo "<pre>" . htmlspecialchars($trace) . "</pre>";
        }
        exit(1);
    }

    private function getErrorType($errno) {
        switch ($errno) {
            case E_ERROR: return 'Fatal Error';
            case E_WARNING: return 'Warning';
            case E_PARSE: return 'Parse Error';
            case E_NOTICE: return 'Notice';
            case E_CORE_ERROR: return 'Core Error';
            case E_CORE_WARNING: return 'Core Warning';
            case E_COMPILE_ERROR: return 'Compile Error';
            case E_COMPILE_WARNING: return 'Compile Warning';
            case E_USER_ERROR: return 'User Error';
            case E_USER_WARNING: return 'User Warning';
            case E_USER_NOTICE: return 'User Notice';
            case E_STRICT: return 'Strict Standards';
            case E_RECOVERABLE_ERROR: return 'Recoverable Error';
            case E_DEPRECATED: return 'Deprecated';
            case E_USER_DEPRECATED: return 'User Deprecated';
            default: return 'Unknown Error';
        }
    }
} 