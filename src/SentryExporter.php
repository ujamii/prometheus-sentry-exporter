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
    public function run() : void
    {
        $gauges = GaugeCollection::withMetricName(MetricName::fromString('sentry_project_open_issues'));

        $projectData = $this->getProjects();
        foreach ($projectData as $project) {
            $gauges->add(
                Gauge::fromValueAndTimestamp($this->getIssueCount($project), time())->withLabels(
                    Label::fromNameAndValue('project_slug', $project->slug),
                    Label::fromNameAndValue('project_name', $project->name)
                )
            );
        }

        HttpResponse::fromMetricCollections($gauges)->withHeader('Content-Type', 'text/plain; charset=utf-8')->respond();
    }

    /**
     * @param \stdClass $project
     *
     * @return float
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getIssueCount(\stdClass $project) : float
    {
        $response = $this->httpClient->request('GET', "projects/{$project->organization->slug}/{$project->slug}/issues/", $this->options);
        $issues   = $this->getJson($response);

        return (float) count($issues);
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getProjects() : array
    {
        $response = $this->httpClient->request('GET', 'projects/', $this->options);

        return $this->getJson($response);
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    protected function getJson(Response $response) : array
    {
        return json_decode($response->getBody()->getContents());
    }

}