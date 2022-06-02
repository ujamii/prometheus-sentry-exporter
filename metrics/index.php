<?php

require_once '../vendor/autoload.php';

$scheme = getenv('HTTP_PROTO') ?: 'https';
$useThrottling = getenv("USE_THROTTLING") == "true" ? True : False;
$exporter = new \Ujamii\OpenMetrics\Sentry\SentryExporter(getenv('AUTH_TOKEN'), $useThrottling, $scheme .'://'. getenv('SENTRY_HOST') .'/api/0/');
$exporter->run();

