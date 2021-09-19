# dnsmanager-mover
Easily update old values in DNS records in bulk.

## How to run
Make sure you have filled in all the right details in the `migrator.php` file (`APP-ID`, `APP-KEY`, `$oldValue` and `$newValue`).
After that, please run the following command in your terminal:

``
php migrator.php
``

This will update all existing DNS records for all the domains associated to your account if the old value is found in the record content.

### Removing old records
You could use the old record remover if, for example, you have stopped using `AAAA` records with the value `::1`. Using the old record remover, it will loop over all your records and check if it matches with your given type and value. If that's the case, the record will be removed.

Make sure you have filled in all the right details in the `remove_old_records.php` file (`APP-ID`, `APP-KEY`, `$recordType` and `$recordValue`).
After that, please run the following command in your terminal:

``
php remove_old_records.php
``

This will update all existing DNS records for all the domains associated to your account.