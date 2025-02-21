# Exporter for sentry data in prometheus format

This package uses the [Sentry](https://sentry.io/) web [api](https://docs.sentry.io/api/) to query for some statistics and outputs them 
in [OpenMetrics](https://github.com/OpenObservability/OpenMetrics) format to be scraped by [prometheus](https://prometheus.io/).

You can also fire it up as a [docker container](#with-docker).

## Usage

Using this exporter with Composer or Docker, you will need the hostname of your sentry installation and an auth token, which you can create 
via `https://<YOUR-SENTRY-HOST>/api/` if you're working with the **Sentry self hosted**. If you're working with **Sentry cloud**, you will 
need to create the token via [`https://sentry.io/settings/account/api/auth-tokens/`](https://sentry.io/settings/account/api/auth-tokens/).

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

The image is based on `php:8.1-apache` and thus exposes data on port 80 by default. Assuming you fire this up with `-p 80:80` on 
localhost, you can see the metrics on http://localhost/metrics.

Configuration is done with 3 env variables: `SENTRY_HOST`, `AUTH_TOKEN`, `USE_THROTTLING` and `HTTP_PROTO`.
The first 2 are mandatory, `HTTP_PROTO` is optional and set to `https` by default. If you're working with the Sentry Cloud, your `SENTRY_HOST` variable must be "sentry.io"
When you set `USE_THROTTLING` to `true/TRUE` or `1`, the exporter will throttle the API requests to prevent a rate limit. This is useful if you have a lot of projects and/or a lot of issues.

```shell
docker run -d --name sentry-prometheus -e SENTRY_HOST=sentry.foobar.com -e AUTH_TOKEN=foobarlongtoken -p "80:80" ghcr.io/ujamii/prometheus-sentry-exporter
```

Docker discontinues support for the free docker hub registry for orgs. The image is now available 
on [GitHub Container Registry](https://github.com/ujamii/prometheus-sentry-exporter/pkgs/container/prometheus-sentry-exporter)

## Output

The script will generate something like:

```
# TYPE sentry_open_issue_events gauge
# HELP sentry_open_issue_events Number of events for one unresolved issue.
sentry_open_issue_events{project_slug="foobar", project_name="Foo Bar", issue_logger="php", issue_type="error", issue_link="https://<SENTRY-HOST>/<ORGANIZATION>/<PROJECT>/issues/1797/", issue_level="error"} 16.000000
...
```

## License and Contribution

[MIT](LICENSE)

As this is OpenSource, you are very welcome to contribute by reporting bugs, improve the code, write tests or 
whatever you are able to do to improve the project.

If you want to do me a favour, buy me something from my [Amazon wishlist](https://www.amazon.de/registry/wishlist/2C7LSRMLEAD4F).
