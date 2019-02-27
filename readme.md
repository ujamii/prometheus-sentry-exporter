# Exporter forsentry data in prometheus format

This package uses the [Sentry](https://sentry.io/) web [api](https://docs.sentry.io/api/) to query for some statistics and 
outputs them in [OpenMetrics](https://github.com/OpenObservability/OpenMetrics) format to be scraped by [prometheus](https://prometheus.io/).

## Installation

```shell
composer req ujamii/prometheus-sentry-exporter
```

## Usage

```php
require_once 'vendor/autoload.php';

$sentryBase = 'https://<YOUR-SENTRY-HOST>/api/0/';
$token      = '<AUTH-TOKEN>'; // get from https://<YOUR-SENTRY-HOST>/api/

$exporter = new \Ujamii\OpenMetrics\Sentry\SentryExporter($token, $sentryBase);
$exporter->run();
```