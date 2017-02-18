<?php
use janyksteenbeek\dnsmanagerMigrator\DNSManager;

require_once 'vendor/autoload.php';

echo "Migrator started at " . date('d-m-Y H:i:s') . PHP_EOL;

$dnsmanager = new DNSManager(
    'https://app.dnsmanager.io',
    'api-id',
    'api-key',
    true
);

$oldIp = '10.0.0.1';
$newIp = '10.0.0.2';


updateDomains($dnsmanager, $oldIp, $newIp);

if($dnsmanager->isReseller) {
    foreach($dnsmanager->getResellerUsers() as $user) {
        updateDomains($dnsmanager, $oldIp, $newIp, $user);
    }
}

function updateDomains($dnsmanager, $oldIp, $newIp, $resellerId = null) {

    foreach($dnsmanager->getDomains($resellerId) as $domain) {

        foreach($dnsmanager->getDomainRecords($domain->id, $resellerId) as $record) {
            if($dnsmanager->updateRecord($domain->id, $record, $oldIp, $newIp, $resellerId)) {
                echo "Updated record " . $record->id . " for " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            }
            else {
                echo "Could not update record " . $record->id . "(" . $record->type . ") for domain " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            }
        }

    }
}