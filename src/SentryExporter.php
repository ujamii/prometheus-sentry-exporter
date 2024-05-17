<?php

namespace Ujamii\OpenMetrics\Sentry;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\HttpResponse;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

class SentryExporter
{

    protected Client $httpClient;

    protected array $options = [];

    protected bool $useThrottling = false;

    public function __construct(string $token, string $sentryBase = 'https://sentry.io/api/0/', bool $useThrottling = false)
    {
        $this->httpClient = new Client([
            'base_uri' => $sentryBase,
        ]);
        $this->options    = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ]
        ];
        $this->useThrottling = $useThrottling;
    }

    /**
     * @throws GuzzleException
     */
    public function run(): void
    {
        $gauges = GaugeCollection::withMetricName(MetricName::fromString('sentry_open_issue_events'))->withHelp('Number of events for one unresolved issue.');

        $projectData = $this->getProjects();
        if($this->useThrottling){
            sleep(1);
        }
        foreach (array("prod", "qa", "staging") as $env)
          foreach ($projectData as $project) {
              $projectIssues = $this->getIssues($project, $env);
              if($this->useThrottling){
                  sleep(1);
              }
              foreach ($projectIssues as $issue) {
                  $gauges->add(
                      Gauge::fromValue($issue->count)->withLabels(
                          Label::fromNameAndValue('project_slug', $project->slug),
                          Label::fromNameAndValue('project_name', $project->name),
                          Label::fromNameAndValue('issue_logger', $issue->logger ?? 'unknown'),
                          Label::fromNameAndValue('issue_type', $issue->type),
                          Label::fromNameAndValue('issue_link', $issue->permalink),
                          Label::fromNameAndValue('issue_level', $issue->level),
                          Label::fromNameAndValue('environment', $env)
                      )
                  );
              }
          }
        }

        HttpResponse::fromMetricCollections($gauges)->withHeader('Content-Type', 'text/plain; charset=utf-8')->respond();
    }

    /**
     * @throws GuzzleException
     */
    protected function getIssues(\stdClass $project, $env): array
    {
        $response = $this->httpClient->request('GET', "projects/{$project->organization->slug}/{$project->slug}/issues/?" . http_build_query([ 'environment' => $env ]), $this->options);

        return $this->getJson($response);
    }

    /**
     * @throws GuzzleException
     */
    protected function getProjects(): array
    {
        $response = $this->httpClient->request('GET', 'projects/', $this->options);

        return $this->getJson($response);
    }

    protected function getJson(Response $response): array
    {
        return json_decode($response->getBody()->getContents());
    }

}
