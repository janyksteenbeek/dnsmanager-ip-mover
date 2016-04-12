<?php
use janyksteenbeek\dnsmanagerMigrator\DNSManager;

require_once 'vendor/autoload.php';

echo "Migrator started at " . date('d-m-Y H:i:s');

$dnsmanager = new DNSManager(
    'app.dnsmanager.cc',
    'APP-ID',
    'APP-KEY'
);

$oldIp = '2.3.4.5';
$newIp = '3.4.5.6';

foreach($dnsmanager->getDomains() as $domain) {

    foreach($dnsmanager->getDomainRecords($domain->id) as $record) {
        if($dnsmanager->updateRecord($domain->id, $record, $oldIp, $newIp)) {
            echo "Updated record " . $record->id . " for " . $domain->domain . PHP_EOL;
        }
        else {
            echo "Could not update record " . $record->id . "(" . $record->type . ") for domain " . $domain->domain . PHP_EOL;
        }
    }

}