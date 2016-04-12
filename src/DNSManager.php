<?php

namespace janyksteenbeek\dnsmanagerMigrator;

use GuzzleHttp\Client;

class DNSManager {

    protected $client;

    /**
     * DNSManager constructor.
     *
     * @param $domain
     * @param $apiId
     * @param $apiKey
     */
    public function __construct($domain, $apiId, $apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://' . $domain . '/api/v1/',
            'auth' => [$apiId, $apiKey],
        ]);
    }

    /**
     * Get all domains for user.
     *
     * @return array
     */
    public function getDomains()
    {
        $domains = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $c = $this->client->get(sprintf('user/domains?perpage=50&page=%d', $pageCount));
            $api = json_decode($c->getBody()->getContents());

            foreach($api->results as $domain) {
                $domains[] = $domain;
            }
        } while(! empty($api->results));


        return $domains;
    }

    /**
     * Get all records associated to domain.
     *
     * @param $domainId
     * @return array
     */
    public function getDomainRecords($domainId)
    {
        $records = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $c = $this->client->get(sprintf('user/domain/%d/records?perpage=50&page=%d', $domainId, $pageCount));
            $api = json_decode($c->getBody()->getContents());

            foreach($api->results as $record) {
                $records[] = $record;
            }
        } while(! empty($api->results));

        return $records;
    }

    /**
     * Update domain record.
     *
     * @param $domainId
     * @param $record
     * @param $oldValue
     * @param $newValue
     * @return bool
     */
    public function updateRecord($domainId, $record, $oldValue, $newValue)
    {
        try {
            $c = $this->client->put(sprintf('user/domain/%d/record/%d', $domainId, $record->id), [
                'json' => [
                    'type' => $record->type,
                    'name' => $record->name,
                    'content' => str_replace($oldValue, $newValue, $record->content),
                    'ttl' => $record->ttl,
                    'prio' => $record->prio,
                ]
            ]);
        } catch (\Exception $e) {
            return false;
        }
        
        return $c->getStatusCode() == 200;
    }
}