<?php

declare(strict_types=1);

/**
 * -----------------------------------------------------------------------------
 * Personal Wellness Blueprint
 * Identifier Migration Tool
 * -----------------------------------------------------------------------------
 *
 * Phase 1
 *
 * - Parse command line
 * - Validate arguments
 * - Build identifier mapping
 * - Display execution plan
 *
 * No filesystem changes are performed.
 * -----------------------------------------------------------------------------
 */

final class IdentifierMigration
{
    private array $config = [];

    private array $mapping = [];
    
    private array $files = [];

private array $plan = [];

private int $filesScanned = 0;

private int $filesWithChanges = 0;

private int $identifierReplacements = 0;

private int $filenameChanges = 0;

private string $backupRoot = '';

private int $filesBackedUp = 0;

private int $filesModified = 0;

private int $filesRenamed = 0;

private int $verificationFailures = 0;

private string $projectRoot;

    public function run(): void
    {
    
    $this->projectRoot = realpath(__DIR__ . '/..');

if ($this->projectRoot === false) {

    $this->fail('Unable to locate project root.');

}
        $this->parseArguments();

        $this->validateArguments();

        $this->buildMapping();

 	$this->scanProject();

	$this->displayPlan();

$this->displayMigrationPlan();

if ($this->config['mode'] === 'dry-run') {

    return;

}

$this->createBackups();

$this->applyMigration();

$this->verifyMigration();

$this->displaySummary();
    }

    /**
     * -------------------------------------------------------------------------
     * Parse CLI Arguments
     * -------------------------------------------------------------------------
     */

    private function parseArguments(): void
    {
        global $argv;

        $this->config = [

            'mode'     => null,
            'from'     => null,
            'to'       => null,
            'backup'   => false,
            'confirm'  => false

        ];

        foreach (array_slice($argv, 1) as $argument) {

            if ($argument === '--dry-run') {

                $this->config['mode'] = 'dry-run';

                continue;

            }

            if ($argument === '--apply') {

                $this->config['mode'] = 'apply';

                continue;

            }

            if ($argument === '--backup') {

                $this->config['backup'] = true;

                continue;

            }

            if ($argument === '--confirm') {

                $this->config['confirm'] = true;

                continue;

            }

            if (str_starts_with($argument, '--from=')) {

                $this->config['from'] = strtoupper(substr($argument, 7));

                continue;

            }

            if (str_starts_with($argument, '--to=')) {

                $this->config['to'] = strtoupper(substr($argument, 5));

                continue;

            }


            $this->fail("Unknown option: {$argument}");
        }
    }

    /**
     * -------------------------------------------------------------------------
     * Validate
     * -------------------------------------------------------------------------
     */

    private function validateArguments(): void
    {
        if ($this->config['mode'] === null) {

            $this->fail(
                "Specify either --dry-run or --apply."
            );

        }

        if ($this->config['from'] === null) {

            $this->fail(
                "Missing required parameter --from."
            );

        }

        if ($this->config['to'] === null) {

            $this->fail(
                "Missing required parameter --to."
            );

        }


        if ($this->config['mode'] === 'apply') {

            if (!$this->config['backup']) {

                $this->fail(
                    "--apply requires --backup."
                );

            }

            if (!$this->config['confirm']) {

                $this->fail(
                    "--apply requires --confirm."
                );

            }

        }
    }

    /**
     * -------------------------------------------------------------------------
     * Build Mapping
     * -------------------------------------------------------------------------
     */

    private function buildMapping(): void
    {

        for ($i = 1; $i <= 999; $i++) {

            $old =
                $this->config['from']
                . str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            $new =
                $this->config['to']
                . str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            $this->mapping[$old] = $new;

        }
    }

    /**
     * -------------------------------------------------------------------------
     * Display Plan
     * -------------------------------------------------------------------------
     */

    private function displayPlan(): void
    {
        $keys = array_keys($this->mapping);

        $first = reset($keys);

        $last = end($keys);

        echo PHP_EOL;

        echo "==================================================" . PHP_EOL;
        echo "Identifier Migration Tool" . PHP_EOL;
        echo "==================================================" . PHP_EOL;

        printf(
            "%-15s %s\n",
            "Mode:",
            strtoupper($this->config['mode'])
        );

        printf(
            "%-15s %s\n",
            "From:",
            $this->config['from']
        );

        printf(
            "%-15s %s\n",
            "To:",
            $this->config['to']
        );

        printf(
            "%-15s %s\n",
            "Backup:",
            $this->config['backup'] ? 'Yes' : 'No'
        );

        printf(
            "%-15s %s\n",
            "Confirm:",
            $this->config['confirm'] ? 'Yes' : 'No'
        );

        printf(
            "%-15s %d\n",
            "Mappings:",
            count($this->mapping)
        );

        printf(
            "%-15s %s → %s\n",
            "First:",
            $first,
            $this->mapping[$first]
        );

        printf(
            "%-15s %s → %s\n",
            "Last:",
            $last,
            $this->mapping[$last]
        );

        printf(
            "%-15s %s\n",
            "Project:",
            $this->projectRoot
        );

        echo "==================================================" . PHP_EOL;
        echo PHP_EOL;
        echo "Phase 1 complete." . PHP_EOL;
        echo "No filesystem changes have been performed." . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * -------------------------------------------------------------------------
     * Error
     * -------------------------------------------------------------------------
     */

    private function fail(string $message): never
    {
        echo PHP_EOL;
        echo "ERROR: {$message}" . PHP_EOL;
        echo PHP_EOL;

        exit(1);
    }
    
private function scanProject(): void
{
    $root = $this->projectRoot;

    $exclude = [
        DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . 'migration-backups' . DIRECTORY_SEPARATOR,
    ];

    $extensions = [
        'php',
        'json',
        'md',
        'txt',
        'html',
        'htm',
        'xml',
        'yml',
        'yaml',
        'css',
        'js'
    ];

    $pattern = '/\\b'
        . preg_quote($this->config['from'], '/')
        . '\\d{3}\\b/';

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $root,
            FilesystemIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $file) {

        if (!$file->isFile()) {
            continue;
        }

        $path = $file->getPathname();

        /*
         * -------------------------------------------------------------
         * Skip excluded folders
         * -------------------------------------------------------------
         */

        foreach ($exclude as $skip) {

            if (strpos($path, $skip) !== false) {
                continue 2;
            }

        }

        /*
         * -------------------------------------------------------------
         * Skip this migration tool
         * -------------------------------------------------------------
         */

        if (realpath($path) === realpath(__FILE__)) {
            continue;
        }

        /*
         * -------------------------------------------------------------
         * Skip audit tools
         * -------------------------------------------------------------
         */

        if (
            basename($path) === 'audit-identifiers.php' ||
            basename($path) === 'audit-food-ids.php'
        ) {
            continue;
        }

        /*
         * -------------------------------------------------------------
         * Skip generated reports
         * -------------------------------------------------------------
         */

        if (
            basename($path) === 'results.txt' ||
            basename($path) === 'report.txt'
        ) {
            continue;
        }

        /*
         * -------------------------------------------------------------
         * Extension filter
         * -------------------------------------------------------------
         */

        if (
            !in_array(
                strtolower($file->getExtension()),
                $extensions,
                true
            )
        ) {
            continue;
        }

        $this->filesScanned++;

        $entry = [

            'path' => $path,

            'rename' => false,

            'content' => []

        ];

        /*
         * -------------------------------------------------------------
         * Filename
         * -------------------------------------------------------------
         */

        if (preg_match($pattern, $file->getFilename())) {

            $entry['rename'] = true;

            $this->filenameChanges++;

        }

        /*
         * -------------------------------------------------------------
         * Contents
         * -------------------------------------------------------------
         */

        $lines = @file($path);

        if ($lines !== false) {

            foreach ($lines as $lineNumber => $line) {

                if (!preg_match_all(
                    $pattern,
                    $line,
                    $matches
                )) {
                    continue;
                }

                foreach ($matches[0] as $id) {

                    $entry['content'][] = [

                        'line' => $lineNumber + 1,

                        'from' => $id,

                        'to' => $this->mapping[$id] ?? null

                    ];

                    $this->identifierReplacements++;

                }

            }

        }

        if (
            $entry['rename'] ||
            !empty($entry['content'])
        ) {

            $this->plan[] = $entry;

            $this->filesWithChanges++;

        }

    }
}
    /**
 * -------------------------------------------------------------------------
 * Display Migration Plan
 * -------------------------------------------------------------------------
 */
private function displayMigrationPlan(): void
	{
    echo PHP_EOL;
    echo "==================================================" . PHP_EOL;
    echo "Migration Plan" . PHP_EOL;
    echo "==================================================" . PHP_EOL;
    echo PHP_EOL;

    if (empty($this->plan)) {

        echo "No changes required." . PHP_EOL;
        echo PHP_EOL;

        return;
    }

    foreach ($this->plan as $file) {

        echo "--------------------------------------------------" . PHP_EOL;
        echo str_replace(
            $this->projectRoot . DIRECTORY_SEPARATOR,
            '',
            $file['path']
        ) . PHP_EOL;
        echo PHP_EOL;

        /*
         * -------------------------------------------------------------
         * Filename
         * -------------------------------------------------------------
         */

        if ($file['rename']) {

            $oldName = basename($file['path']);

            $newName = str_replace(
                array_keys($this->mapping),
                array_values($this->mapping),
                $oldName
            );

            echo "Rename" . PHP_EOL;
            echo "    {$oldName}" . PHP_EOL;
            echo "      ↓" . PHP_EOL;
            echo "    {$newName}" . PHP_EOL;
            echo PHP_EOL;

        }

        /*
         * -------------------------------------------------------------
         * Content Changes
         * -------------------------------------------------------------
         */

        if (!empty($file['content'])) {

            echo "Contents" . PHP_EOL;

            foreach ($file['content'] as $change) {

                printf(
                    "    Line %-5d %s → %s\n",
                    $change['line'],
                    $change['from'],
                    $change['to']
                );

            }

            echo PHP_EOL;

        }

    }

    echo "==================================================" . PHP_EOL;
    echo "Migration Plan Summary" . PHP_EOL;
    echo "==================================================" . PHP_EOL;

    printf(
        "%-28s %d\n",
        "Files scanned:",
        $this->filesScanned
    );

    printf(
        "%-28s %d\n",
        "Files requiring changes:",
        $this->filesWithChanges
    );

    printf(
        "%-28s %d\n",
        "Filename changes:",
        $this->filenameChanges
    );

    printf(
        "%-28s %d\n",
        "Identifier replacements:",
        $this->identifierReplacements
    );

    echo "==================================================" . PHP_EOL;
    echo PHP_EOL;
}
    
/**
 * -------------------------------------------------------------------------
 * Create Backups
 * -------------------------------------------------------------------------
 */
private function createBackups(): void
{
    echo PHP_EOL;
    echo "Creating backups..." . PHP_EOL;

    $this->backupRoot =
        $this->projectRoot
        . DIRECTORY_SEPARATOR
        . 'migration-backups'
        . DIRECTORY_SEPARATOR
        . date('Ymd-His');

    if (!is_dir($this->backupRoot)) {

        if (!mkdir($this->backupRoot, 0777, true) && !is_dir($this->backupRoot)) {

            $this->fail(
                "Unable to create backup folder:\n{$this->backupRoot}"
            );

        }

    }

    foreach ($this->plan as $entry) {

        $source = $entry['path'];

        $relative = substr(
            $source,
            strlen($this->projectRoot) + 1
        );

        $destination =
            $this->backupRoot
            . DIRECTORY_SEPARATOR
            . $relative;

        $directory = dirname($destination);

        if (!is_dir($directory)) {

            mkdir($directory, 0777, true);

        }

        if (!copy($source, $destination)) {

            $this->fail(
                "Unable to backup {$relative}"
            );

        }

        $this->filesBackedUp++;

    }

    echo "Backups created: {$this->filesBackedUp}" . PHP_EOL;
    echo "Location: {$this->backupRoot}" . PHP_EOL;
}

/**
 * -------------------------------------------------------------------------
 * Apply Migration
 * -------------------------------------------------------------------------
 */
private function applyMigration(): void
{
    echo PHP_EOL;
    echo "Applying migration..." . PHP_EOL;

    foreach ($this->plan as $entry) {

        // Read the file

        $contents = file_get_contents($entry['path']);

        if ($contents === false) {
            $this->fail("Unable to read {$entry['path']}");
        }

        // Replace every identifier in the mapping

        $contents = str_replace(
            array_keys($this->mapping),
            array_values($this->mapping),
            $contents
        );

        // Write it back

        if (file_put_contents($entry['path'], $contents) === false) {
            $this->fail("Unable to write {$entry['path']}");
        }

        $this->filesModified++;

        // Rename file if required

        if ($entry['rename']) {

            $oldName = $entry['path'];

            $newName = dirname($oldName)
                . DIRECTORY_SEPARATOR
                . str_replace(
                    array_keys($this->mapping),
                    array_values($this->mapping),
                    basename($oldName)
                );

            if (!rename($oldName, $newName)) {
                $this->fail("Unable to rename {$oldName}");
            }

            $this->filesRenamed++;
        }
    }

    echo "Migration complete." . PHP_EOL;
}
/**
 * -------------------------------------------------------------------------
 * Verify Migration
 * -------------------------------------------------------------------------
 */
private function verifyMigration(): void
{
    echo PHP_EOL;
    echo "Verifying migration..." . PHP_EOL;

    /*
     * Reset scan state
     */

    $this->plan = [];

    $this->filesScanned = 0;

    $this->filesWithChanges = 0;

    $this->filenameChanges = 0;

    $this->identifierReplacements = 0;

    /*
     * Scan the entire project again
     */

    $this->scanProject();

    /*
     * Any remaining items in the plan represent
     * legacy identifiers that still need migrating.
     */

    $this->verificationFailures = count($this->plan);

    if ($this->verificationFailures === 0) {

        echo "Verification successful." . PHP_EOL;

        return;

    }

    echo PHP_EOL;
    echo "Verification FAILED" . PHP_EOL;
    echo "-------------------" . PHP_EOL;

    foreach ($this->plan as $entry) {

        echo str_replace(
            $this->projectRoot . DIRECTORY_SEPARATOR,
            '',
            $entry['path']
        ) . PHP_EOL;

    }

    echo PHP_EOL;

    echo $this->verificationFailures
        . " file(s) still contain legacy identifiers."
        . PHP_EOL;
}
/**
 * -------------------------------------------------------------------------
 * Display Summary
 * -------------------------------------------------------------------------
 */
private function displaySummary(): void
{
    echo PHP_EOL;
    echo "==================================================" . PHP_EOL;
    echo "Migration Summary" . PHP_EOL;
    echo "==================================================" . PHP_EOL;

    printf("%-30s %d\n", "Files scanned:", $this->filesScanned);
    printf("%-30s %d\n", "Files requiring changes:", $this->filesWithChanges);

    echo PHP_EOL;

    printf("%-30s %d\n", "Files backed up:", $this->filesBackedUp);
    printf("%-30s %d\n", "Files modified:", $this->filesModified);
    printf("%-30s %d\n", "Files renamed:", $this->filesRenamed);
    printf("%-30s %d\n", "Verification failures:", $this->verificationFailures);

    echo PHP_EOL;

    printf("%-30s %s\n", "Backup location:", $this->backupRoot);

    echo "==================================================" . PHP_EOL;
    echo PHP_EOL;
}
    
}

(new IdentifierMigration())->run();