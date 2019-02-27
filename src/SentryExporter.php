<?php

namespace Ujamii\OpenMetrics\Sentry;

use GuzzleHttp\Psr7\Response;
use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\HttpResponse;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
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
        $counters = CounterCollection::withMetricName(MetricName::fromString('sentry_project'));

        $projectData = $this->getProjects();
        foreach ($projectData as $project) {
            $counters->add(
                Counter::fromValueAndTimestamp($this->getIssueCount($project), time())->withLabels(
                    Label::fromNameAndValue('project_slug', $project->slug),
                    Label::fromNameAndValue('project_name', $project->name)
                )
            );
        }

        HttpResponse::fromMetricCollections($counters)->withHeader('Content-Type', 'text/plain; charset=utf-8')->respond();
    }

    /**
     * @param \stdClass $project
     *
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getIssueCount(\stdClass $project) : int
    {
        $response = $this->httpClient->request('GET', "projects/{$project->organization->slug}/{$project->slug}/issues/", $this->options);
        $issues   = $this->getJson($response);

        return count($issues);
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