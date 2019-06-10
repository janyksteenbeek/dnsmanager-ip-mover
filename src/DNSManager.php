<?php

namespace janyksteenbeek\dnsmanagerMigrator;

use GuzzleHttp\Client;

class DNSManager {

    protected $client;
    public $isReseller = false;

    /**
     * DNSManager constructor.
     *
     * @param $domain
     * @param $apiId
     * @param $apiKey
     * @param $isReseller bool
     */
    public function __construct($domain, $apiId, $apiKey, $isReseller = false)
    {
        $this->client = new Client([
            'base_uri' => $domain . '/api/v1/',
            'auth' => [$apiId, $apiKey],
        ]);

        $this->isReseller = $isReseller;
    }

    /**
     * Get all domains for user.
     *
     * @param null $resellerId
     * @return array
     */
    public function getDomains($resellerId = null)
    {
        $domains = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $c = $this->client->get(sprintf('user/domains?perpage=50&page=%d', $pageCount) . ($resellerId ? '&reseller_user=' . $resellerId : null));
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
     * @param null $resellerId
     * @return array
     */
    public function getDomainRecords($domainId, $resellerId = null)
    {
        $records = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $c = $this->client->get(sprintf('user/domain/%d/records?perpage=50&page=%d', $domainId, $pageCount) . ($resellerId ? '&reseller_user=' . $resellerId : null));
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
     * @param null $resellerId
     * @return bool
     */
    public function updateRecord($domainId, $record, $oldValue, $newValue, $resellerId = null)
    {
        try {
            $c = $this->client->put(sprintf('user/domain/%d/record/%d', $domainId, $record->id) . ($resellerId ? '?reseller_user=' . $resellerId : null), [
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

    /**
     * Delete domain record.
     *
     * @param $domainId
     * @param $record
     * @param null $resellerId
     * @return bool
     */
    public function deleteRecord($domainId, $record, $resellerId = null)
    {
        try {
            $c = $this->client->delete(sprintf('user/domain/%d/record/%d', $domainId, $record->id) . ($resellerId ? '?reseller_user=' . $resellerId : null));
        } catch (\Exception $e) {
            return false;
        }

        return $c->getStatusCode() == 200;
    }

    
    /**
     * Get list with user IDs that are under your reseller.
     *
     * @return array
     */
    public function getResellerUsers() {
        if(! $this->isReseller) {
            return false;
        }

        $users = [];
        $pageCount = 0;

        do {
            $pageCount++;

            $c = $this->client->get(sprintf('reseller/users?perpage=50&page=%d', $pageCount));
            $api = json_decode($c->getBody()->getContents());

            foreach($api->results as $user) {
                $users[] = $user->id;
            }
        } while(! empty($api->results));


        return $users;
    }
}