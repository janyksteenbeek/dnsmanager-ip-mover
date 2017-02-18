<?php
use janyksteenbeek\dnsmanagerMigrator\DNSManager;

require_once 'vendor/autoload.php';

echo "Migrator started at " . date('d-m-Y H:i:s') . PHP_EOL;

$dnsmanager = new DNSManager(
    'https://app.dnsmanager.io',
    '4761b7a0-aac7-4198-ab55-51fcb7d5c881',
    '5Hq28uWUqdN7MSJges12u4UoWKgK39hY',
    true
);

$oldIp = '185.114.226.82';
$newIp = '136.144.131.121';


// updateDomains($dnsmanager, $oldIp, $newIp);

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