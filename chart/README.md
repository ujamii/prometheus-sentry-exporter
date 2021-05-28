# Prometheus Sentry Exporter

This is the chart to deploy the sentry exporter
with helm.

## Installing the Chart

To install a release named `sentry-exporter`:

```bash
helm install sentry exporter \
  --set sentry.host=sentry.foobar.com \
  --set sentry.authToken=foobarlongtoken \
  ./
```

## Prometheus Scrape

By default, the pod has been annotated with the
 following values.

```yaml
prometheus.io/scrape: 'true'
prometheus.io/port: '80'
prometheus.io/metrics: '/metrics/'
```

The default configuration for prometheus server
 is able to recognize the annotation and scrape
 the metrics.
