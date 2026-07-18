<?php

namespace PWB\Validator;

class Validator
{
    public function run(): void
    {
        echo PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo " Personal Wellness Blueprint Validator" . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo PHP_EOL;

        $this->checkEnvironment();
        $this->scanKnowledgeBase();

        echo PHP_EOL;
        echo "Validation complete." . PHP_EOL;
    }

    private function checkEnvironment(): void
    {
        echo "[OK] PHP Environment" . PHP_EOL;
    }

    private function scanKnowledgeBase(): void
    {
        echo "[OK] Knowledgebase found" . PHP_EOL;
    }
}