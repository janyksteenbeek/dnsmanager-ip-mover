<?php
use janyksteenbeek\dnsmanagerMigrator\DNSManager;

require_once 'vendor/autoload.php';

echo "Old record remover started at " . date('d-m-Y H:i:s') . PHP_EOL;

$dnsmanager = new DNSManager(
    'https://app.dnsmanager.io',
    'api-id',
    'api-key',
    true
);

$recordType = 'AAAA';
$recordValue = '::1';


updateDomains($dnsmanager, $recordType, $recordValue);

if($dnsmanager->isReseller) {
    foreach($dnsmanager->getResellerUsers() as $user) {
        updateDomains($dnsmanager, $recordType, $recordValue, $user);
    }
}

function updateDomains(DNSManager $dnsmanager, $recordType, $recordValue, $resellerId = null) {

    foreach($dnsmanager->getDomains($resellerId) as $domain) {

        foreach($dnsmanager->getDomainRecords($domain->id, $resellerId) as $record) {
            if($record->type != $recordType || $record->content != $recordValue) {
                echo "Skipping " . $record->id . " for " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
                continue;
            }

            if($dnsmanager->deleteRecord($domain->id, $record, $resellerId)) {
                echo "Deleted record " . $record->id . " for " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            }
            else {
                echo "Could not delete record " . $record->id . "(" . $record->type . ") for domain " . $domain->domain . ($resellerId ? ' (' . $resellerId . ')' : '') . PHP_EOL;
            }
        }

    }
}
