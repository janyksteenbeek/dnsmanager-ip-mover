<?php

namespace JanykSteenbeek\DNSManager\Mover;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private GuzzleClient $client;
    private bool $isReseller;

    /**
     * DNSManager constructor.
     *
     * @param string $domain
     * @param string $apiId
     * @param string $apiKey
     * @param bool $isReseller
     */
    public function __construct(string $domain, string $apiId, string $apiKey, bool $isReseller = false)
    {
        $this->client = new GuzzleClient([
            'base_uri' => $domain . '/api/v1/',
            'auth' => [$apiId, $apiKey],
        ]);

        $this->isReseller = $isReseller;
    }

    /**
     * Get all domains for user.
     *
     * @param string|null $resellerId
     * @return array
     * @throws GuzzleException
     */
    public function getDomains(?string $resellerId = null): array
    {
        $domains = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $c = $this->client->get(sprintf('user/domains?perpage=50&page=%d', $pageCount) . ($resellerId ? '&reseller_user=' . $resellerId : null));
            $api = json_decode($c->getBody()->getContents());

            foreach ($api->results as $domain) {
                $domains[] = $domain;
            }
        } while (! empty($api->results));

        return $domains;
    }

    /**
     * Get all records associated to domain.
     *
     * @param string $domainId
     * @param string|null $resellerId
     * @return array
     * @throws GuzzleException
     */
    public function getDomainRecords(string $domainId, ?string $resellerId = null): array
    {
        $records = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $c = $this->client->get(sprintf('user/domain/%d/records?perpage=50&page=%d', $domainId, $pageCount) . ($resellerId ? '&reseller_user=' . $resellerId : null));
            $api = json_decode($c->getBody()->getContents());

            foreach ($api->results as $record) {
                $records[] = $record;
            }
        } while (! empty($api->results));

        return $records;
    }

    /**
     * Update domain record.
     *
     * @param string $domainId
     * @param object $record
     * @param string $oldValue
     * @param string $newValue
     * @param string|null $resellerId
     * @return bool|null
     */
    public function updateRecord(string $domainId, object $record, string $oldValue, string $newValue, ?string $resellerId = null): ?bool
    {
        if (! str_contains($record->content, $oldValue)) {
            return null;
        }

        try {
            $response = $this->client->put(sprintf('user/domain/%d/record/%d', $domainId, $record->id) . ($resellerId ? '?reseller_user=' . $resellerId : null), [
                'json' => [
                    'type' => $record->type,
                    'name' => $record->name,
                    'content' => str_replace($oldValue, $newValue, $record->content),
                    'ttl' => $record->ttl,
                    'prio' => $record->prio,
                ],
            ]);
        } catch (GuzzleException $e) {
            return false;
        }

        return $response->getStatusCode() === 200;
    }

    /**
     * Delete domain record.
     *
     * @param string $domainId
     * @param string $record
     * @param string|null $resellerId
     * @return bool
     */
    public function deleteRecord(string $domainId, string $record, ?string $resellerId = null): bool
    {
        try {
            $response = $this->client->delete(sprintf('user/domain/%d/record/%d', $domainId, $record->id) . ($resellerId ? '?reseller_user=' . $resellerId : null));
        } catch (GuzzleException $e) {
            return false;
        }

        return $response->getStatusCode() === 200;
    }

    /**
     * Get list with user IDs that are under your reseller.
     *
     * @return array|null
     * @throws GuzzleException
     */
    public function getResellerUsers(): ?array
    {
        if (! $this->isReseller) {
            return null;
        }

        $users = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $response = $this->client->get(sprintf('reseller/users?perpage=50&page=%d', $pageCount));
            $api = json_decode($response->getBody()->getContents());

            foreach ($api->results as $user) {
                $users[] = $user->id;
            }
        } while (! empty($api->results));


        return $users;
    }

    /**
     * @return bool
     */
    public function isReseller(): bool
    {
        return $this->isReseller;
    }
}
