# dnsmanager-migrator
Migrate all your A-records from your old IP to a new IP address.

## How to run
Make sure you have filled in all the right details in the `migrator.php` file (`APP-ID`, `APP-KEY`, `$oldIp` and `$newIp`).
After that, please run the following command in your terminal:

``
php migrator.php
``

This will update all existing DNS records for all the domains associated to your account.

## dnsmanager? Whats that?

[dnsmanager.cc][dnsmanager] is an awesome _free_ DNS hosting solution, which you can use to manage all your domain names with.
Note they also have an awesome reseller function which allows you as a hosting company to use their service fully whitelabeled.

[Check it out.][dnsmanager]

[dnsmanager]: https://app.dnsmanager.cc
