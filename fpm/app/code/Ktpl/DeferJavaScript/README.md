# Deferred JavaScript extension for Magento 2

## Module version
  * 1.0.0

## Module Compatibility
  * Magento Open Source 2.3.x, Magento Commerce 2.3.x, Magento Commerce Cloud 2.3.x

## Description
  * This extension allows to enable deferred JavaScript loading at Magento 2 store. All JS code automatically moved to the bottom of the page (before <body> tag end). This speeds up page load and increases page speed score.

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