<?php

declare(strict_types=1);

// Author by Lab | zefry

require __DIR__ . '/../src/Runtime/Qualification.php';

use OneQay\Runtime\Qualification\QualificationOutcome;
use OneQay\Runtime\Qualification\RuntimeQualificationEvaluator;
use OneQay\Runtime\Qualification\SanitizedRuntimeEvidenceParser;

$options = getopt('', ['evidence:']);
$path = $options['evidence'] ?? null;

if (!is_string($path) || $path === '') {
    fwrite(STDERR, "Usage: php tools/runtime-qualification.php --evidence=/path/to/sanitized-evidence.json\n");
    exit(64);
}

if (!is_file($path) || !is_readable($path)) {
    fwrite(STDERR, "Evidence file is not readable.\n");
    exit(64);
}

try {
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Unable to read evidence file.');
    }

    $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new InvalidArgumentException('Evidence payload must be a JSON object.');
    }

    $evidence = (new SanitizedRuntimeEvidenceParser())->parse($decoded);
    $report = (new RuntimeQualificationEvaluator())->evaluate($evidence);

    fwrite(STDOUT, $report->toJson());
    exit($report->outcome === QualificationOutcome::EVIDENCE_COMPLETE ? 0 : 2);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Qualification input rejected: ' . $exception->getMessage() . "\n");
    exit(64);
}
