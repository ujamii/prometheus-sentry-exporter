<?php

namespace Ujamii\OpenMetrics\Sentry;

use GuzzleHttp\Psr7\Response;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\HttpResponse;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

/**
 * Class SentryExporter
 * @package Ujamii\OpenMetrics\Sentry
 */
class SentryExporter
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * SentryExporter constructor.
     *
     * @param string $token
     * @param string $sentryBase
     */
    public function __construct(string $token, string $sentryBase = 'https://sentry.io/api/0/')
    {
        $this->httpClient = new \GuzzleHttp\Client([
            'base_uri' => $sentryBase,
        ]);
        $this->options    = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ]
        ];
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function run(): void
    {
        $gauges = GaugeCollection::withMetricName(MetricName::fromString('sentry_open_issue_events'))->withHelp('Number of events for one unresolved issue.');

        $projectData = $this->getProjects();
        foreach ($projectData as $project) {
            $projectIssues = $this->getIssues($project);
            foreach ($projectIssues as $issue) {
                $gauges->add(
                    Gauge::fromValue($issue->count)->withLabels(
                        Label::fromNameAndValue('project_slug', $project->slug),
                        Label::fromNameAndValue('project_name', $project->name),
                        Label::fromNameAndValue('issue_logger', $issue->logger ?? 'unknown'),
                        Label::fromNameAndValue('issue_type', $issue->type),
                        Label::fromNameAndValue('issue_link', $issue->permalink),
                        Label::fromNameAndValue('issue_level', $issue->level)
                    )
                );
            }
        }

        HttpResponse::fromMetricCollections($gauges)->withHeader('Content-Type', 'text/plain; charset=utf-8')->respond();
    }

    /**
     * @param \stdClass $project
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getIssues(\stdClass $project): array
    {
        $response = $this->httpClient->request('GET', "projects/{$project->organization->slug}/{$project->slug}/issues/", $this->options);

        return $this->getJson($response);
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getProjects(): array
    {
        $response = $this->httpClient->request('GET', 'projects/', $this->options);

        return $this->getJson($response);
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    protected function getJson(Response $response): array
    {
        return json_decode($response->getBody()->getContents());
    }

}
