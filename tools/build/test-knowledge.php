<?php

declare(strict_types=1);

require_once __DIR__ . '/../../engine/src/Knowledge/KnowledgeBase.php';

use PWB\Knowledge\KnowledgeBase;

$kb = new KnowledgeBase();

echo "Foods: " . count($kb->foods()) . PHP_EOL;

echo "Bioactives: " . count($kb->bioactives()) . PHP_EOL;

echo "Mechanisms: " . count($kb->mechanisms()) . PHP_EOL;

echo "Body Systems: " . count($kb->bodySystems()) . PHP_EOL;

echo PHP_EOL;

echo "FD001 exists: ";
echo $kb->foodExists('FD001') ? "YES" : "NO";
echo PHP_EOL;

echo "OS002 exists: ";
echo $kb->bioactiveExists('OS002') ? "YES" : "NO";
echo PHP_EOL;

echo "MEC071 exists: ";
echo $kb->mechanismExists('MEC071') ? "YES" : "NO";
echo PHP_EOL;