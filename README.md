WebCheckout payU latam SDK Magento 2
============================================================

## Description ##
payU latam SDK gateway payment available for Argentina, Brasil, Chile, Colombia, México, Panamá, Perú

## Table of Contents

* [Installation](#installation)
* [Configuration](#configuration)


## Installation ##

Use composer package manager

```bash
composer require saulmoralespa/magento2-payulatamsdk
```

Execute the commands

```bash
php bin/magento module:enable Saulmoralespa_PayuLatamSDK --clear-static-content
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy en_US #on i18n
```

## Configuration ##

### 1. Enter the configuration menu of the payment method ###
![Enter the configuration menu of the payment method](https://3.bp.blogspot.com/-3otn_wB8Nrs/XcDLLwvOaFI/AAAAAAAACz8/wzbx0w7TwEAns7nr_8cWWyE7PxWD2ChjwCLcBGAsYHQ/s1600/magento2-payulatamsdk.png)