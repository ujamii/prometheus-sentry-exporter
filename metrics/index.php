<?php

require_once '../vendor/autoload.php';

$scheme = getenv('HTTP_PROTO') ?: 'https';
$useThrottling = strtolower(getenv("USE_THROTTLING")) === "true" || getenv("USE_THROTTLING") === "1";
$exporter = new \Ujamii\OpenMetrics\Sentry\SentryExporter(getenv('AUTH_TOKEN'), $scheme .'://'. getenv('SENTRY_HOST') .'/api/0/', $useThrottling);
$exporter->run();

