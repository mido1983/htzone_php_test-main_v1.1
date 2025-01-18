<?php

class Logger {
    private $logPath;
    private static $instance = null;

    private function __construct() {
        $this->logPath = __DIR__ . '/../logs/';
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }

    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }

    public function debug($message, $context = []) {
        if (getenv('APP_DEBUG') === 'true') {
            $this->log('DEBUG', $message, $context);
        }
    }

    private function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message $contextStr\n";
        
        $filename = $this->logPath . date('Y-m-d') . '.log';
        file_put_contents($filename, $logMessage, FILE_APPEND);
    }

    public function getRecentLogs($lines = 100) {
        $filename = $this->logPath . date('Y-m-d') . '.log';
        if (!file_exists($filename)) {
            return [];
        }

        $logs = [];
        $file = new SplFileObject($filename, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        
        $start = max(0, $lastLine - $lines);
        $file->seek($start);
        
        while (!$file->eof()) {
            $logs[] = $file->fgets();
        }
        
        return $logs;
    }
} 