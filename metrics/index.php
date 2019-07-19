<?php

require_once '../vendor/autoload.php';

$scheme = getenv('HTTP_PROTO') ?: 'https';
$exporter = new \Ujamii\OpenMetrics\Sentry\SentryExporter(getenv('AUTH_TOKEN'), $scheme .'://'. getenv('SENTRY_HOST') .'/api/0/');
$exporter->run();

