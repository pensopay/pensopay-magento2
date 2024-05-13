## PensoPay_Magento2

Module PensoPay\Gateway implements integration with the pensopay payment service provider.

Tested in Magento 2.1.2 - 2.4.6

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
composer require pensopay/magento2
php bin/magento module:enable PensoPay_Gateway
php bin/magento setup:upgrade
php bin/magento setup:di:compile
``` 

### Hyvä Theme Support
```
composer require hyva-themes/magento2-hyva-checkout-pensopay-payment
```


**Please note that FTP installation will not work as this module has requirements that will be auto installed when using composer**
