# Conflict Detector extension for Magento 2

## Module version
  * 1.0.0

## Module Compatibility
  * Magento Open Source 2.3.x, Magento Commerce 2.3.x, Magento Commerce Cloud 2.3.x

## Description
  * Magento 2 Conflict Detector extension allows you to detect class rewrite conflicts easy in Magento 2 Store. To see the conflicts list please install the module and navigate to Magento 2 Admin Panel > System > Tools > Conflict Detector.

## Demo URLs
  * Backend URL: http://module.solution.magentoprojects.net/webadmin (admin/admin123)
  * Frontend URL: http://module.solution.magentoprojects.net/

## How to install
  * In your Magento 2 root directory create folder app/code/Ktpl/Wishlist
  * Copy files and folders from archive to that folder
  * In command line, using "cd", navigate to your Magento 2 root directory
  * Run commands:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Test Cases