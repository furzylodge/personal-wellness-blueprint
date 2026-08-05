<?php

declare(strict_types=1);

namespace PWB\Build;

use RuntimeException;

abstract class BuildTask
{
    protected array $warnings = [];
    protected array $errors = [];

    protected float $startTime;

    public function run(): void
    {
        $this->startTime = microtime(true);

        try {

            $this->load();

            $this->validate();

            if (!empty($this->errors)) {
                $this->buildFailed();
                return;
            }

            $this->build();

            $this->write();

            $this->buildSucceeded();

        }
        catch (\Throwable $e) {

            $this->errors[] = $e->getMessage();

            $this->buildFailed();

        }
    }

    abstract protected function load(): void;

    abstract protected function validate(): void;

    abstract protected function build(): void;

    abstract protected function write(): void;

    abstract protected function recordCount(): int;

    abstract protected function outputFile(): string;

    protected function warning(string $message): void
    {
        $this->warnings[] = $message;
    }

    protected function error(string $message): void
    {
        $this->errors[] = $message;
    }

    private function buildSucceeded(): void
    {
        $elapsed = microtime(true) - $this->startTime;

        echo PHP_EOL;
        echo "==================================================" . PHP_EOL;
        echo "BUILD SUCCESSFUL" . PHP_EOL;
        echo "==================================================" . PHP_EOL;
        echo "Records  : " . $this->recordCount() . PHP_EOL;
        echo "Warnings : " . count($this->warnings) . PHP_EOL;
        echo "Errors   : " . count($this->errors) . PHP_EOL;
        echo "Output   : " . $this->displayPath($this->outputFile()) . PHP_EOL;
        echo "Time     : " . number_format($elapsed,3) . " seconds" . PHP_EOL;
        echo "==================================================" . PHP_EOL;

        if (!empty($this->warnings)) {

            echo PHP_EOL . "Warnings" . PHP_EOL;
            echo "--------" . PHP_EOL;

            foreach ($this->warnings as $warning) {
                echo "- {$warning}" . PHP_EOL;
            }
        }

        echo PHP_EOL;
    }

    private function buildFailed(): void
    {
        echo PHP_EOL;
        echo "==================================================" . PHP_EOL;
        echo "BUILD FAILED" . PHP_EOL;
        echo "==================================================" . PHP_EOL;

        foreach ($this->errors as $error) {
            echo "ERROR: {$error}" . PHP_EOL;
        }

        echo PHP_EOL;

        exit(1);
    }
    
    protected function displayPath(string $path): string
    {
    $root = realpath(__DIR__ . '/../../../');

    $path = realpath($path);

    if ($root !== false && $path !== false) {

        $path = str_replace($root . DIRECTORY_SEPARATOR, '', $path);

    }

    return str_replace('\\', '/', $path);
    }
}
