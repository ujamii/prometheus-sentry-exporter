<?php

require_once '../vendor/autoload.php';

$exporter = new \Ujamii\OpenMetrics\Sentry\SentryExporter(getenv('AUTH_TOKEN'), 'https://'. getenv('SENTRY_HOST') .'/api/0/');
$exporter->run();

