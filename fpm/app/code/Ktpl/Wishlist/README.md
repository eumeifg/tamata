# Wishlist extension for Magento 2

## Module version
  * 1.0.0

## Module Compatibility
  * Magento Open Source 2.3.x, Magento Commerce 2.3.x, Magento Commerce Cloud 2.3.x

## Description
  * This extension designed to allow your customers to add items to wishlist in an engaging and simple way. Unregistered customers will ask to login or continue shopping at the time of adding product to wishlist

## Feature list
  * Allows adding all items from product listing page and product detail page to existing customer wishlist after logging in.
  * Not logged-in customer will get an option to logged in or continue shopping with which they stay on the same page as they were before.

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