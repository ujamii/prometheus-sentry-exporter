# Exporter for sentry data in prometheus format

This package uses the [Sentry](https://sentry.io/) web [api](https://docs.sentry.io/api/) to query for some statistics and 
outputs them in [OpenMetrics](https://github.com/OpenObservability/OpenMetrics) format to be scraped by [prometheus](https://prometheus.io/).

You can also fire it up as a [docker container](#with-docker).

## Usage

In both cases, you will need the hostname of your sentry installation and an auth token, which you can create via
`https://<YOUR-SENTRY-HOST>/api/`

### with Composer

**Installation**

```shell
composer req ujamii/prometheus-sentry-exporter
```

**Usage in your custom file**

```php
require_once 'vendor/autoload.php';

$sentryBase = 'https://<YOUR-SENTRY-HOST>/api/0/';
$token      = '<AUTH-TOKEN>'; // get from https://<YOUR-SENTRY-HOST>/api/

$exporter = new \Ujamii\OpenMetrics\Sentry\SentryExporter($token, $sentryBase);
$exporter->run();
```

### with Docker

The image is based on `php:7.2-apache` and thus exposes data on port 80 by default. Assuming you fire this up with `-p 80:80` on localhost,
you can see the metrics on http://localhost/metrics.

Configuration is done with 2 env variables: `SENTRY_HOST` and `AUTH_TOKEN`.

```shell
docker run -d --name sentry-prometheus -e SENTRY_HOST=sentry.foobar.com -e AUTH_TOKEN=foobarlongtoken -p "80:80" ujamii/prometheus-sentry-exporter
```

View on [Docker Hub](https://hub.docker.com/r/ujamii/prometheus-sentry-exporter)

## Output

The script will generate something like:

```
# TYPE sentry_open_issue_events gauge
# HELP sentry_open_issue_events Number of events for one unresolved issue.
sentry_open_issue_events{project_slug="foobar", project_name="Foo Bar", issue_first_seen="2019-02-19T11:24:52Z", issue_last_seen="2019-02-28T09:17:47Z", issue_logger="php", issue_type="error", issue_link="https://<SENTRY-HOST>/<ORGANIZATION>/<PROJECT>/issues/1797/"} 16.000000
...
```