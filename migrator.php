<?php

require_once 'vendor/autoload.php';

use JanykSteenbeek\DNSManager\Mover\Client;

echo "Mover started at " . date('d-m-Y H:i:s') . PHP_EOL;

$client = new Client(
    'https://app.dnsmanager.io',
    'api-id',
    'api-key',
    true
);

$oldValue = '10.0.0.1';
$newValue = '10.0.0.2';

echo "Replacing \"${oldValue}\" with \"${$newValue}\"..." . PHP_EOL;

updateDomains($client, $oldValue, $newValue);

if ($client->isReseller()) {
    foreach ($client->getResellerUsers() as $user) {
        updateDomains($client, $oldValue, $newValue, $user);
    }
}

function updateDomains(Client $client, string $oldValue, $newValue, $resellerId = null)
{
    foreach ($client->getDomains($resellerId) as $domain) {
        foreach ($client->getDomainRecords($domain->id, $resellerId) as $record) {
            $updated = $client->updateRecord($domain->id, $record, $oldValue, $newValue, $resellerId);
            if ($updated === true) {
                echo "Updated record " . $record->id . " for " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            } elseif ($updated === false) {
                echo "Could not update record " . $record->id . "(" . $record->type . ") for domain " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            }
        }
    }
}
