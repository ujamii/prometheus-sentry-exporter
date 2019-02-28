# Exporter for sentry data in prometheus format

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
will generate something like:

```
# TYPE sentry_open_issue_events gauge
# HELP sentry_open_issue_events Number of events for one unresolved issue.
sentry_open_issue_events{project_slug="foobar", project_name="Foo Bar", issue_first_seen="2019-02-19T11:24:52Z", issue_last_seen="2019-02-28T09:17:47Z", issue_logger="php", issue_type="error", issue_link="https://<SENTRY-HOST>/<ORGANIZATION>/<PROJECT>/issues/1797/"} 16.000000 1551366084
...
```
