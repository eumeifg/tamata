E-Commerce Platform

# Magento Dockerized

- [Magento Dockerized](#magento-dockerized)
  - [TL;DR](#tldr)
  - [How to execute bin/magento CLI commands?](#how-to-execute-binmagento-cli-commands)
  - [How to update the db?](#how-to-update-the-db)
  - [what about cronjobs?](#what-about-cronjobs)
  - [Common issues](#common-issues)
  - [clone the Git repo from Adobe cloud](#clone-the-git-repo-from-adobe-cloud)
  - [Get all the code to your machine](#get-all-the-code-to-your-machine)
  - [How to add an admin?](#how-to-add-an-admin)
  - [How to tell if Redis is being utilized as session storage?](#how-to-tell-if-redis-is-being-utilized-as-session-storage)
  - [how to check if Magento is setting a TTL for Redis keys?](#how-to-check-if-magento-is-setting-a-ttl-for-redis-keys)
  - [you should be able to see the number of keys that are set to expire. you can also see the maximum memory set to Redis and its eviction policy](#you-should-be-able-to-see-the-number-of-keys-that-are-set-to-expire-you-can-also-see-the-maximum-memory-set-to-redis-and-its-eviction-policy)
  - [how to check if Sentinel is working?](#how-to-check-if-sentinel-is-working)
  - [you should be able to see the number of keys that are set to expire. you can also see the maximum memory set to Redis and its eviction policy](#you-should-be-able-to-see-the-number-of-keys-that-are-set-to-expire-you-can-also-see-the-maximum-memory-set-to-redis-and-its-eviction-policy-1)
  - [How to tell if Elasticsearch is being utilized for indexing?](#how-to-tell-if-elasticsearch-is-being-utilized-for-indexing)
  - [Useful MySQL commands for Magento](#useful-mysql-commands-for-magento)
  - [how to access the shell for FPM container?](#how-to-access-the-shell-for-fpm-container)
    - [configure SSO profile](#configure-sso-profile)
    - [SSO login](#sso-login)
    - [list all the pods](#list-all-the-pods)
    - [access the required pod ssh](#access-the-required-pod-ssh)
    - [set  the required cluster](#set--the-required-cluster)
    - [delete a pod](#delete-a-pod)
  - [Usefull magento CLI commands](#usefull-magento-cli-commands)
    - [setup](#setup)
    - [sampledata](#sampledata)
    - [module](#module)
    - [indexer](#indexer)
    - [Available commands:](#available-commands)
    - [admin](#admin)
    - [cache](#cache)
    - [catalog](#catalog)
    - [cron](#cron)
    - [customer](#customer)
    - [deploy](#deploy)
    - [dev](#dev)
  - [how to clean up the db?](#how-to-clean-up-the-db)

## TL;DR

To run this installation in a local environment :

1. prepare the images and spin the stack

```
docker-compose up --build
```

2. import the DB dump into MySql (if you have an empty DB) , run the below command from your local development machine. MariaDB port is already exposed.

```
mysql -u root -pmagento2 magento2 < "Y:\download\Tamata_local_development.sql"
```

3. open [https://127.0.0.1](https://127.0.0.1) and [https://127.0.0.1/admin](https://127.0.0.1/admin)

---

## How to execute bin/magento CLI commands?

attach to a fpm container and execute the command you want:

```
$/app/bin/magento setup:upgrade
$/app/bin/magento help

```

---

## How to update the db?

attach to a fpm container and execute:

```
$composer dump-autoload
$/app/bin/magento setup:upgrade --keep-generated 
$/app/bin/magento app:config:import
```

---

## what about cronjobs?

the plan is to use Kubernetes cronjobs to execute the below command in a container every minute:

```
$/app/bin/magento cron:run 
```

---

## Common issues

| error                                           | description                                                                                                                                                                   |
| ----------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| There has been an error processing your request | Magento is connected to the DB but there is an error , check_var/log/exception.log_ . goto[http://127.0.0.1/admin](http://127.0.0.1/admin) to check if the admin page is working |
| To many redirects                               | The URLs are not set correctly in core_config_data table.<br />update the url records in "**core-config-data**" table .                                                 |
| An error has happened during application run.   | log to fpm container, then run "**cat var/log/exceptions.log**"<br />or<br />log to fpm container, then run "**cat var/log/report/exceptions.log"**              |

## clone the Git repo from Adobe cloud

Add your machine SSH keys to Adobe repo then clone it

```
git clone --branch production ateks2tmbdnts@git.eu-5.magento.cloud:ateks2tmbdnts.git tamata-production
```

---

## Get all the code to your machine

to get the required php packages with composer (this will save composer cache to your /temp directory)

```
docker run --rm --interactive --tty  --volume $PWD:/app --volume ~/temp:/tmp composer install --ignore-platform-reqs -vvv
```

---

## How to add an admin?

In the fpm container , attach to the shell then execute :

```
  $php /app/bin/magento admin:user:create --admin-user=salman --admin-password=admin@123 --admin-email=msalman@creativeadvtech.com --admin-firstname=Salman --admin-lastname=Mohd
```

---

## How to tell if Redis is being utilized as session storage?

attach to a redis container then run :

```
redis-cli monitor
```

now open a page in magento and watch the redis logs.

---

## how to check if Magento is setting a TTL for Redis keys?

attach to a redis container then run :

```
redis-cli info
```

you should be able to see the number of keys that are set to expire. you can also see the maximum memory set to Redis and its eviction policy
---------------------------------------------------------------------------------------------------------------------------------------------

## how to check if Sentinel is working?

attach to a redis container then run :

```
redis-cli -p 26379 
sentinel CKQUORUM mymaster
```

you should be able to see the number of keys that are set to expire. you can also see the maximum memory set to Redis and its eviction policy
---------------------------------------------------------------------------------------------------------------------------------------------

## How to tell if Elasticsearch is being utilized for indexing?

attach to an EL container to watch the logs then attach to the fpm container and execute:

```
$/app/bin/magento indexer:reindex
$/app/bin/magento cache:clean
$/app/bin/magento cache:flush
```

---

## Useful MySQL commands for Magento

```
# Check the cron jobs progress
SELECT COUNT(*), `status` FROM cron_schedule GROUP BY `status`
UNION
SELECT COUNT(*) , "total" FROM  cron_schedule

# Check cron jobs status
SELECT COUNT(*), `status` , job_code FROM cron_schedule  GROUP BY `status` , job_code ORDER BY STATUS, COUNT(*) desc

# Watch files export progress
SELECT * FROM queue_message_status where queue_id =4 ORDER BY id DESC

# Watch messages stuck at retiring
SELECT * FROM `ateks2tmbdnts`.`queue_message_status` WHERE STATUS=5;
```

## how to access the shell for FPM container?

* for docker installations, use VScode and attach to the container.

- for kubernetes clusters on AWS, use SSO login:

### configure SSO profile

aws configure sso

### SSO login

```
aws sso login --profile saif
```

### list all the pods

```
kubectl get po -n tamata
```

### access the required pod ssh

```
kubectl exec -ti magento-fpm-8654876687-trxkf -n tamata -- sh
```

### set  the required cluster

```
aws eks update-kubeconfig --name eks-tamata-prod --alias eks-tamata-prod --region eu-central-1 --profile saif
aws eks update-kubeconfig --name eks-tamata-staging --alias eks-tamata-staging --region eu-central-1 --profile saif
```

### delete a pod

```
kubectl delete pod magento-fpm-7c64675485-hv2m6 -n tamata
```

---

## Usefull magento CLI commands

### setup

* `setup:backup` Takes backup of Magento Application code base, media and database
* `setup:config:set` Creates or modifies the deployment configuration
* `setup:cron:run`Runs cron job scheduled for setup application
* `setup:db-data:upgrade `Installs and upgrades data in the DB
* `setup:db-schema:upgrade `Installs and upgrades the DB schema
* `setup:db:status `Checks if DB schema or data requires upgrade
* `setup:di:compile `Generates DI configuration and all missing classes that can be auto-generated
* `setup:install` Installs the Magento application
* `setup:performance:generate-fixtures `Generates fixtures
* `setup:rollback`Rolls back Magento Application codebase, media and database
* `setup:static-content:deploy `Deploys static view files
* `setup:store-config:set `Installs the store configuration
* `setup:uninstall `Uninstalls the Magento application
* `setup:upgrade` Upgrades the Magento application, DB data, and schema

### sampledata

* `sampledata:deploy `Deploy sample data modules
* `sampledata:remove` Remove all sample data packages from composer.json
* `sampledata:reset `Reset all sample data modules for re-installation

### module

* `module:disable`Disables specified modules
* `module:enable` Enables specified modules
* `module:status` Displays status of modules
* `module:uninstall Uninstalls modules installed by composer`

### indexer

* `indexer:info` Shows allowed Indexers
* `indexer:reindex `Reindexes Data
* `indexer:reset` Resets indexer status to invalid
* `indexer:set-mode `Sets index mode type
* `indexer:show-mode `Shows Index Mode
* `indexer:status`Shows status of Indexer

### Available commands:

* `help` Displays help for a command
* `list` Lists commands

### admin

* `admin:user:create `Creates an administrator
* `admin:user:unlock `Unlock Admin Account

### cache

* `cache:clean` Cleans cache type(s)
* `cache:disable` Disables cache type(s)
* `cache:enable` Enables cache type(s)
* `cache:flush` Flushes cache storage used by cache type(s)
* `cache:status` Checks cache status

### catalog

* `catalog:images:resize `Creates resized product images
* `catalog:product:attributes:cleanup `Removes unused product attributes.

### cron

* `cron:run` Runs jobs by schedule

### customer

* `customer:hash:upgrade Upgrade customer's hash according to the latest algorithm`

### deploy

* `deploy:mode:set `Set application mode.
* `deploy:mode:show `Displays current application mode.

### dev

* `dev:source-theme:deploy `Collects and publishes source files for theme.
* `dev:tests:run` Runs tests
* `dev:urn-catalog:generate `Generates the catalog of URNs to *.xsd mappings for the IDE to highlight xml.
* `dev:xml:convert `Converts XML file using XSL style sheets

---

## how to clean up the db?

you can cleanup the db for test using the below query, but it might cause some issues for some plugins:

```SET

#Truncate order tables
TRUNCATE TABLE `gift_message`;
TRUNCATE TABLE `quote`;
TRUNCATE TABLE `quote_address`;
TRUNCATE TABLE `quote_address_item`;
TRUNCATE TABLE `quote_id_mask`;
TRUNCATE TABLE `quote_item`;
TRUNCATE TABLE `quote_item_option`;
TRUNCATE TABLE `quote_payment`;
TRUNCATE TABLE `quote_shipping_rate`;
TRUNCATE TABLE `reporting_orders`;
TRUNCATE TABLE `sales_bestsellers_aggregated_daily`;
TRUNCATE TABLE `sales_bestsellers_aggregated_monthly`;
TRUNCATE TABLE `sales_bestsellers_aggregated_yearly`;
TRUNCATE TABLE `sales_creditmemo`;
TRUNCATE TABLE `sales_creditmemo_comment`;
TRUNCATE TABLE `sales_creditmemo_grid`;
TRUNCATE TABLE `sales_creditmemo_item`;
TRUNCATE TABLE `sales_invoice`;
TRUNCATE TABLE `sales_invoiced_aggregated`;
TRUNCATE TABLE `sales_invoiced_aggregated_order`;
TRUNCATE TABLE `sales_invoice_comment`;
TRUNCATE TABLE `sales_invoice_grid`;
TRUNCATE TABLE `sales_invoice_item`;
TRUNCATE TABLE `sales_order`;
TRUNCATE TABLE `sales_order_address`;
TRUNCATE TABLE `sales_order_aggregated_created`;
TRUNCATE TABLE `sales_order_aggregated_updated`;
TRUNCATE TABLE `sales_order_grid`;
TRUNCATE TABLE `sales_order_item`;
TRUNCATE TABLE `sales_order_payment`;
TRUNCATE TABLE `sales_order_status_history`;
TRUNCATE TABLE `sales_order_tax`;
TRUNCATE TABLE `sales_order_tax_item`;
TRUNCATE TABLE `sales_payment_transaction`;
TRUNCATE TABLE `sales_refunded_aggregated`;
TRUNCATE TABLE `sales_refunded_aggregated_order`;
TRUNCATE TABLE `sales_shipment`;
TRUNCATE TABLE `sales_shipment_comment`;
TRUNCATE TABLE `sales_shipment_grid`;
TRUNCATE TABLE `sales_shipment_item`;
TRUNCATE TABLE `sales_shipment_track`;
TRUNCATE TABLE `sales_shipping_aggregated`;
TRUNCATE TABLE `sales_shipping_aggregated_order`;
TRUNCATE TABLE `tax_order_aggregated_created`;
TRUNCATE TABLE `tax_order_aggregated_updated`;
TRUNCATE TABLE `md_vendor_order_status_log`;
TRUNCATE TABLE `reporting_users`;
TRUNCATE TABLE `url_rewrite`;
TRUNCATE TABLE `magedelight_sociallogin`;
TRUNCATE TABLE `salesrule_coupon`;
TRUNCATE TABLE `catalog_product_index_price_replica`;
TRUNCATE TABLE `md_vendor_order_log`;
TRUNCATE TABLE `search_query`; 
TRUNCATE TABLE `md_vendor_product_store`; 

#Truncate Customer tables

TRUNCATE TABLE `customer_address_entity`;
TRUNCATE TABLE `customer_address_entity_datetime`;
TRUNCATE TABLE `customer_address_entity_decimal`;
TRUNCATE TABLE `customer_address_entity_int`;
TRUNCATE TABLE `customer_address_entity_text`;
TRUNCATE TABLE `customer_address_entity_varchar`;
TRUNCATE TABLE `customer_entity`;
TRUNCATE TABLE `customer_entity_datetime`;
TRUNCATE TABLE `customer_entity_decimal`;
TRUNCATE TABLE `customer_entity_int`;
TRUNCATE TABLE `customer_entity_text`;
TRUNCATE TABLE `customer_entity_varchar`;
TRUNCATE TABLE `customer_grid_flat`;
TRUNCATE TABLE `customer_log`;
TRUNCATE TABLE `customer_log`;
TRUNCATE TABLE `customer_visitor`;
TRUNCATE TABLE `persistent_session`;
TRUNCATE TABLE `wishlist`;
TRUNCATE TABLE `wishlist_item`;
TRUNCATE TABLE `wishlist_item_option`;

#Truncate Review tables

TRUNCATE TABLE `review`;
TRUNCATE TABLE `review_detail`;
TRUNCATE TABLE `review_entity_summary`;
TRUNCATE TABLE `review_store`;

#Truncate Product tables

TRUNCATE TABLE `cataloginventory_stock_item`;
TRUNCATE TABLE `cataloginventory_stock_status`;
TRUNCATE TABLE `cataloginventory_stock_status_idx`;
TRUNCATE TABLE `cataloginventory_stock_status_tmp`;
TRUNCATE TABLE `catalog_category_product`;
TRUNCATE TABLE `catalog_category_product_index`;
TRUNCATE TABLE `catalog_category_product_index_tmp`;
TRUNCATE TABLE `catalog_compare_item`;
TRUNCATE TABLE `catalog_product_bundle_option`;
TRUNCATE TABLE `catalog_product_bundle_option_value`;
TRUNCATE TABLE `catalog_product_bundle_price_index`;
TRUNCATE TABLE `catalog_product_bundle_selection`;
TRUNCATE TABLE `catalog_product_bundle_selection_price`;
TRUNCATE TABLE `catalog_product_bundle_stock_index`;
TRUNCATE TABLE `catalog_product_entity`;
TRUNCATE TABLE `catalog_product_entity_datetime`;
TRUNCATE TABLE `catalog_product_entity_decimal`;
TRUNCATE TABLE `catalog_product_entity_gallery`;
TRUNCATE TABLE `catalog_product_entity_int`;
TRUNCATE TABLE `catalog_product_entity_media_gallery`;
TRUNCATE TABLE `catalog_product_entity_media_gallery_value`;
TRUNCATE TABLE `catalog_product_entity_media_gallery_value_to_entity`;
TRUNCATE TABLE `catalog_product_entity_media_gallery_value_video`;
TRUNCATE TABLE `catalog_product_entity_text`;
TRUNCATE TABLE `catalog_product_entity_tier_price`;
TRUNCATE TABLE `catalog_product_entity_varchar`;
TRUNCATE TABLE `catalog_product_index_eav`;
TRUNCATE TABLE `catalog_product_index_eav_decimal`;
TRUNCATE TABLE `catalog_product_index_eav_decimal_idx`;
TRUNCATE TABLE `catalog_product_index_eav_decimal_tmp`;
TRUNCATE TABLE `catalog_product_index_eav_idx`;
TRUNCATE TABLE `catalog_product_index_eav_tmp`;
TRUNCATE TABLE `catalog_product_index_price`;
TRUNCATE TABLE `catalog_product_index_price_bundle_idx`;
TRUNCATE TABLE `catalog_product_index_price_bundle_opt_idx`;
TRUNCATE TABLE `catalog_product_index_price_bundle_opt_tmp`;
TRUNCATE TABLE `catalog_product_index_price_bundle_sel_idx`;
TRUNCATE TABLE `catalog_product_index_price_bundle_sel_tmp`;
TRUNCATE TABLE `catalog_product_index_price_bundle_tmp`;
TRUNCATE TABLE `catalog_product_index_price_cfg_opt_agr_idx`;
TRUNCATE TABLE `catalog_product_index_price_cfg_opt_agr_tmp`;
TRUNCATE TABLE `catalog_product_index_price_cfg_opt_idx`;
TRUNCATE TABLE `catalog_product_index_price_cfg_opt_tmp`;
TRUNCATE TABLE `catalog_product_index_price_downlod_idx`;
TRUNCATE TABLE `catalog_product_index_price_downlod_tmp`;
TRUNCATE TABLE `catalog_product_index_price_final_idx`;
TRUNCATE TABLE `catalog_product_index_price_final_tmp`;
TRUNCATE TABLE `catalog_product_index_price_idx`;
TRUNCATE TABLE `catalog_product_index_price_opt_agr_idx`;
TRUNCATE TABLE `catalog_product_index_price_opt_agr_tmp`;
TRUNCATE TABLE `catalog_product_index_price_opt_idx`;
TRUNCATE TABLE `catalog_product_index_price_opt_tmp`;
TRUNCATE TABLE `catalog_product_index_price_tmp`;
TRUNCATE TABLE `catalog_product_index_tier_price`;
TRUNCATE TABLE `catalog_product_index_website`;
TRUNCATE TABLE `catalog_product_link`;
TRUNCATE TABLE `catalog_product_link_attribute_decimal`;
TRUNCATE TABLE `catalog_product_link_attribute_int`;
TRUNCATE TABLE `catalog_product_link_attribute_varchar`;
TRUNCATE TABLE `catalog_product_option`;
TRUNCATE TABLE `catalog_product_option_price`;
TRUNCATE TABLE `catalog_product_option_title`;
TRUNCATE TABLE `catalog_product_option_type_price`;
TRUNCATE TABLE `catalog_product_option_type_title`;
TRUNCATE TABLE `catalog_product_option_type_value`;
TRUNCATE TABLE `catalog_product_relation`;
TRUNCATE TABLE `catalog_product_super_attribute`;
TRUNCATE TABLE `catalog_product_super_attribute_label`;
TRUNCATE TABLE `catalog_product_super_link`;
TRUNCATE TABLE `catalog_product_website`;
TRUNCATE TABLE `catalog_url_rewrite_product_category`;
TRUNCATE TABLE `downloadable_link`;
TRUNCATE TABLE `downloadable_link_price`;
TRUNCATE TABLE `downloadable_link_purchased`;
TRUNCATE TABLE `downloadable_link_purchased_item`;
TRUNCATE TABLE `downloadable_link_title`;
TRUNCATE TABLE `downloadable_sample`;
TRUNCATE TABLE `downloadable_sample_title`;
TRUNCATE TABLE `product_alert_price`;
TRUNCATE TABLE `product_alert_stock`;
TRUNCATE TABLE `report_compared_product_index`;
TRUNCATE TABLE `report_viewed_product_aggregated_daily`;
TRUNCATE TABLE `report_viewed_product_aggregated_monthly`;
TRUNCATE TABLE `report_viewed_product_aggregated_yearly`;
TRUNCATE TABLE `report_viewed_product_index`;

# these tables sent by Salman
TRUNCATE TABLE `ktpl_devicetokens`;
TRUNCATE TABLE `ktpl_pushnotification`;
TRUNCATE TABLE `ktpl_pushnotification_transaction`;
TRUNCATE TABLE `ktpl_recent_view_product_list`;
TRUNCATE TABLE `ktpl_search_index`;
TRUNCATE TABLE `ktpl_search_landing_page`;
TRUNCATE TABLE `ktpl_search_score_rule`;
TRUNCATE TABLE `ktpl_search_score_rule_index`;
TRUNCATE TABLE `ktpl_search_score_rule_product_cl`;
TRUNCATE TABLE `ktpl_search_stopword`;
TRUNCATE TABLE `ktpl_search_synonym`;
TRUNCATE TABLE `ktpl_warehousemanagement_warehousemanagement`;
TRUNCATE TABLE `ktpl_abandon_cart_list`;

TRUNCATE TABLE `amasty_xsearch_users_search`;
TRUNCATE TABLE `amasty_xsearch_related_term`;
TRUNCATE TABLE `amasty_xsearch_category_fulltext_scope2`;
TRUNCATE TABLE `amasty_xsearch_category_fulltext_scope1`;

TRUNCATE TABLE `md_abandonedcart_email_black_list`;
TRUNCATE TABLE `md_abandonedcart_email_black_list_stores`;
TRUNCATE TABLE `md_abandonedcart_email_queue`;
TRUNCATE TABLE `md_abandonedcart_history`;
TRUNCATE TABLE `md_abandonedcart_reports`;
TRUNCATE TABLE `md_abandonedcart_report_daily`;
TRUNCATE TABLE `md_abandonedcart_report_monthly`;
TRUNCATE TABLE `md_abandonedcart_report_yearly`;
TRUNCATE TABLE `md_abandonedcart_rule`;
TRUNCATE TABLE `md_abandonedcart_schedule`;
TRUNCATE TABLE `md_commissions`;
TRUNCATE TABLE `md_eav_attribute_placeholder`;
TRUNCATE TABLE `md_eav_attribute_tooltip`;
TRUNCATE TABLE `md_microsite_url_rewrite_cl`;
TRUNCATE TABLE `md_product_filter_setting`;
TRUNCATE TABLE `md_search_store_data_temporary`;
TRUNCATE TABLE `md_selling_categories_request`;
TRUNCATE TABLE `md_vendor`;
TRUNCATE TABLE `md_vendor_category`;
TRUNCATE TABLE `md_vendor_category_commission`;
TRUNCATE TABLE `md_vendor_commissions`;
TRUNCATE TABLE `md_vendor_commission_invoice`;
TRUNCATE TABLE `md_vendor_commission_invoice_payment`;
TRUNCATE TABLE `md_vendor_commission_payment`;
TRUNCATE TABLE `md_vendor_group_commission`;
TRUNCATE TABLE `md_vendor_microsites`;
TRUNCATE TABLE `md_vendor_order`;
TRUNCATE TABLE `md_vendor_order_log`;
TRUNCATE TABLE `md_vendor_order_status_log`;
TRUNCATE TABLE `md_vendor_payment_history`;
TRUNCATE TABLE `md_vendor_product`;
TRUNCATE TABLE `md_vendor_product_bulk_request`;
TRUNCATE TABLE `md_vendor_product_listing_cl`;
TRUNCATE TABLE `md_vendor_product_listing_idx`;
TRUNCATE TABLE `md_vendor_product_request`;
TRUNCATE TABLE `md_vendor_product_request_store`;
TRUNCATE TABLE `md_vendor_product_request_super_link`;
TRUNCATE TABLE `md_vendor_product_request_website`;
TRUNCATE TABLE `md_vendor_product_store`;
TRUNCATE TABLE `md_vendor_product_website`;
TRUNCATE TABLE `md_vendor_rating`;
TRUNCATE TABLE `md_vendor_rating_rating_type`;
TRUNCATE TABLE `md_vendor_status_request`;
TRUNCATE TABLE `md_vendor_product_website`;
TRUNCATE TABLE `md_vendor_website_data`;


TRUNCATE TABLE `sequence_creditmemo_0`;
TRUNCATE TABLE `sequence_creditmemo_1`;
TRUNCATE TABLE `sequence_creditmemo_2`;
TRUNCATE TABLE `sequence_invoice_0`;
TRUNCATE TABLE `sequence_invoice_1`;
TRUNCATE TABLE `sequence_invoice_2`;
TRUNCATE TABLE `sequence_order_0`;
TRUNCATE TABLE `sequence_order_1`;
TRUNCATE TABLE `sequence_order_2`;
TRUNCATE TABLE `sequence_product`;
TRUNCATE TABLE `sequence_product_bundle_option`;
TRUNCATE TABLE `sequence_product_bundle_selection`;
TRUNCATE TABLE `sequence_rma_item_0`;
TRUNCATE TABLE `sequence_rma_item_1`;
TRUNCATE TABLE `sequence_rma_item_2`;
TRUNCATE TABLE `sequence_salesrule`;
TRUNCATE TABLE `sequence_shipment_0`;
TRUNCATE TABLE `sequence_shipment_1`;
TRUNCATE TABLE `sequence_shipment_2`;

TRUNCATE TABLE `magento_logging_event`;
TRUNCATE TABLE `magento_logging_event_changes`;
TRUNCATE TABLE `magento_logging_event`;
TRUNCATE TABLE `catalog_category_product_index`;
TRUNCATE TABLE `catalog_category_product_index_replica`;
TRUNCATE TABLE `catalog_category_product_index_store1`;
TRUNCATE TABLE `catalog_category_product_index_store1_replica`;
TRUNCATE TABLE `catalog_category_product_index_store2`;
TRUNCATE TABLE `catalog_category_product_index_store2_replica`;
TRUNCATE TABLE `catalog_category_product_index_tmp`;
SET FOREIGN_KEY_CHECKS = 1;
```
