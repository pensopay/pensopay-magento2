## Pensopay_Gateway

Important: This plugin supports pensopay v2 payments via app.pensopay.com. If you are not sure it’s the right one, please reach out to us before proceeding.

With pensopay payments v2 you are able to integrate pensopay v2 gateway on your Magento 2 Store.
In moments you will be able to receive payments via credit card, MobilePay, Apple Pay, Viabill, Anyday, Swish.

Tested in Magento 2.4.4 - 2.4.9

Module supports:
* Authorize
* Capture
* Partial Capture
* Refund
* Partial Refund
* Cancel
* Payment Fees
* Multiple Stores with Multiple Accounts

### Installation
```
composer require pensopay/magento2-gateway
php bin/magento module:enable Pensopay_Gateway
php bin/magento setup:upgrade
php bin/magento setup:di:compile
``` 

**Please note that FTP installation will not work as this module has requirements that will be auto installed when using composer**

### Running unit tests
Unit tests live in `Test/Unit` and run with Magento's PHPUnit setup from the root of a Magento install:
```
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist vendor/pensopay/magento2-gateway/Test/Unit
```
(Use `app/code/Pensopay/Gateway/Test/Unit` instead if the module is installed under `app/code`.)
