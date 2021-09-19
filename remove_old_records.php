<?php

require_once 'vendor/autoload.php';

use JanykSteenbeek\DNSManager\Mover\Client;

echo "Old record remover started at " . date('d-m-Y H:i:s') . PHP_EOL;

$dnsmanager = new Client(
    'https://app.dnsmanager.io',
    'api-id',
    'api-key',
    true
);

$recordType = 'AAAA';
$recordValue = '::1';

echo "Removing \"${recordType}\" with value \"${recordValue}\"..." . PHP_EOL;

updateDomains($dnsmanager, $recordType, $recordValue);

if ($dnsmanager->isReseller()) {
    foreach ($dnsmanager->getResellerUsers() as $user) {
        updateDomains($dnsmanager, $recordType, $recordValue, $user);
    }
}

function updateDomains(Client $client, string $recordType, string $recordValue, ?string $resellerId = null)
{
    foreach ($client->getDomains($resellerId) as $domain) {
        foreach ($client->getDomainRecords($domain->id, $resellerId) as $record) {
            if ($record->type !== $recordType || $record->content !== $recordValue) {
                echo "Skipping " . $record->id . " for " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;

                continue;
            }

            if ($client->deleteRecord($domain->id, $record, $resellerId)) {
                echo "Deleted record " . $record->id . " for " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            } else {
                echo "Could not delete record " . $record->id . "(" . $record->type . ") for domain " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            }
        }
    }
}
