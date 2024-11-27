<?php

declare(strict_types=1);

$autoload = dirname(__DIR__).'/vendor/autoload.php';
if (!file_exists($autoload)) {
    throw new RuntimeException('Composer install required');
}

require_once $autoload;

$cloverXmlFile = dirname(__DIR__).'/var/reports/artifacts/clover.xml';
if (!file_exists($cloverXmlFile)) {
    throw new RuntimeException('PhpUnit clover.xml report is required');
}

$cloverXml = new SimpleXMLElement(file_get_contents($cloverXmlFile));
$statements = (int) $cloverXml->project->metrics['statements'];
$coveredStatements = (int) $cloverXml->project->metrics['coveredstatements'];
$percentage = number_format($coveredStatements / $statements * 100, 2);
$minimum = $argv[1];

if ($percentage < $minimum) {
    $message = sprintf(
        '%.2f percent of code is covered; minimum of %.2f percent required',
        $percentage,
        $minimum
    );
    throw new RuntimeException($message);
}

echo sprintf("%.2f percent of code is covered with tests\n", $percentage);
