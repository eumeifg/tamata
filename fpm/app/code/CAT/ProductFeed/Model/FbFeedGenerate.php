<?php

namespace CAT\ProductFeed\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * FbFeedGenerate class
 */
class FbFeedGenerate {

    const WEBSITE_LOCALE = 'general/locale/code';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var array
     */
    protected $categoryList = [];

    /**
     * FbFeedGenerate constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceConnection
     * @param CurrencyFactory $currencyFactory
     * @param File $file
     * @param StoreRepositoryInterface $storeRepository
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param TimezoneInterface $timezone
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        CurrencyFactory $currencyFactory,
        File $file,
        StoreRepositoryInterface $storeRepository,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        TimezoneInterface $timezone
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_resourceConnection = $resourceConnection;
        $this->currencyFactory = $currencyFactory;
        $this->file = $file;
        $this->directoryList = $directoryList;
        $this->storeRepository = $storeRepository;
        $this->_filesystem = $filesystem;
        $this->timezone = $timezone;
        $this->mediaDirectory = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @param int $storeId
     * @return void
     */
    public function generate($storeId)
    {
        $logger = $this->getLogger();
        try {
            $storeLocale = $this->scopeConfig->getValue(self::WEBSITE_LOCALE, ScopeInterface::SCOPE_STORE, $storeId);
            //$lang = explode('_', $storeLocale)[0];
            $products = $this->getProductDetails($storeId);
            $data = $this->addCategories($products, $storeId);
            $feeds = 'default-fb-feed.csv';
            $this->createFeeds($data, $feeds);
        } catch (\Exception $e) {
            $logger->info('Error in generate() : '. $e->getMessage());
        }
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getProductDetails($storeId) {
        try {
            $logger = $this->getLogger();
            $baseUrl = $this->scopeConfig->getValue('web/unsecure/base_url');
            $mediaUrl = $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
            $connection = $this->_resourceConnection->getConnection();
            $query = "SELECT DISTINCT cpe.sku AS id, cpe.entity_id,
            CASE
                WHEN t.value IS NOT NULL THEN t.value
                ELSE (SELECT value from catalog_product_entity_varchar WHERE row_id = cpe.row_id AND attribute_id = 73  AND store_id = 0)
            END AS title,
            des.value AS 'description',
            GROUP_CONCAT(DISTINCT ccp.category_id) as categories,
            brop.value AS brand,
            CASE
                WHEN coop.value Is NOT NULL THEN coop.value
                ELSE (SELECT value from eav_attribute_option_value WHERE option_id = co.value AND store_id = 0)
            END AS color,
            CASE
                WHEN (cpe.type_id IN ('configurable', 'simple') AND mvpw.price IS NOT NULL AND mvpw.price > 0) THEN round(mvpw.price, 2)
                WHEN (cpe.type_id = 'configurable' AND min(p.value) IS NOT NULL) THEN round(min(p.value), 2)
                ELSE (SELECT round(min(value), 2) FROM catalog_product_entity_decimal WHERE row_id = cpe.row_id AND attribute_id = 77  AND store_id = 0)
            End AS price,
            CASE
            WHEN (cpe.type_id IN ('configurable', 'simple') AND mvpw.special_price IS NOT NULL AND mvpw.special_price > 0) THEN round(mvpw.special_price, 2)
                WHEN cpe.type_id = 'configurable' AND min(sp.value) IS NOT NULL THEN round(min(sp.value), 2)
                ELSE (SELECT round(min(value), 2) FROM catalog_product_entity_decimal WHERE row_id = cpe.row_id AND attribute_id = 78  AND store_id = 0)
            End AS sale_price,
            /*spd.value AS sale_price_from_date,
            std.value AS sale_price_to_date,*/
            mvpw.special_from_date AS sale_price_from_date,
            mvpw.special_to_date AS sale_price_to_date,
            CASE
                WHEN url.value IS NOT NULL THEN concat('".$baseUrl."', url.value, '.html')
                ELSE (SELECT CONCAT('".$baseUrl."', value, '.html') FROM catalog_product_entity_varchar WHERE row_id = cpe.row_id AND attribute_id = 124  AND store_id = 0)
            END AS link,
            concat('".$mediaUrl."', im.value) AS image_link,
            mvwd.business_name AS vendor_name,
            mvp.qty AS quantity
            FROM catalog_product_entity AS cpe
            LEFT JOIN catalog_product_entity_int AS status ON status.row_id = cpe.row_id AND status.attribute_id = 97 and status.store_id = 0
            LEFT JOIN catalog_product_entity_text AS des ON des.row_id = cpe.row_id AND des.attribute_id = 75 and des.store_id = 0
            LEFT JOIN catalog_product_entity_varchar AS t ON t.row_id = cpe.row_id AND t.attribute_id = 73 AND t.store_id = 0
            LEFT JOIN catalog_product_entity_varchar AS url ON url.row_id = cpe.row_id AND url.attribute_id = 124 AND url.store_id = 0
            LEFT JOIN catalog_product_entity_varchar AS im ON im.row_id = cpe.row_id AND im.attribute_id = 87 AND im.store_id  = 0
            LEFT JOIN catalog_product_entity_int AS vis ON vis.row_id = cpe.row_id AND vis.attribute_id = 99 AND im.store_id  = 0
            LEFT JOIN catalog_category_product AS ccp ON ccp.product_id = cpe.row_id
            LEFT JOIN catalog_product_entity_int AS br ON br.row_id = cpe.row_id and br.attribute_id = 287 and br.store_id = 0
            LEFT JOIN eav_attribute_option_value AS brop ON brop.option_id = br.value and brop.store_id  = 0
            LEFT JOIN catalog_product_entity_int AS co ON co.row_id = cpe.row_id and co.attribute_id = 93 and co.store_id = 0
            LEFT JOIN eav_attribute_option_value AS coop ON coop.option_id = co.value AND coop.store_id = 0
            LEFT JOIN catalog_product_relation AS cpr ON cpr.parent_id = cpe.row_id
            LEFT JOIN catalog_product_entity_decimal AS p ON p.row_id = cpr.child_id AND p.attribute_id = 77 AND p.store_id = 0
            LEFT JOIN catalog_product_entity_decimal AS sp ON sp.row_id = cpr.child_id AND sp.attribute_id = 78 AND sp.store_id = 0
            /*LEFT JOIN catalog_product_entity_datetime AS spd ON spd.row_id = cpe.row_id AND spd.attribute_id = 79 AND spd.store_id = 0
            LEFT JOIN catalog_product_entity_datetime AS std ON std.row_id = cpe.row_id AND std.attribute_id = 80 AND std.store_id = 0*/
            LEFT JOIN md_vendor_product AS mvp ON mvp.marketplace_product_id = cpe.entity_id
            LEFT JOIN md_vendor_website_data AS mvwd ON mvwd.vendor_id = mvp.vendor_id
            LEFT JOIN md_vendor_product_website AS mvpw ON mvpw.vendor_product_id = mvp.vendor_product_id
            WHERE vis.value = '4' AND status.value = '1' AND mvpw.status= '1' /*AND cpe.sku = '1000-11-10-10-11-7'*/ GROUP BY id, title, link, image_link, 'description', brand, color/*, sale_price_from_date, sale_price_to_date*/  /*LIMIT 150*/ ";
            return $connection->fetchAll($query);
        } catch (\Exception $e) {
            $logger->info('Error on query: '. $e->getMessage());
        }
    }

    /**
     * @param $products
     * @param $storeId
     * @return \string[][]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addCategories($products, $storeId) {
        $connection = $this->_resourceConnection->getConnection();
        $currency = trim($this->getStoreCurrency($storeId));
        $queryList = "SELECT cce.entity_id, ccev.value as name, cce.path as path FROM catalog_category_entity as cce LEFT JOIN catalog_category_entity_varchar as ccev ON ccev.row_id = cce.row_id AND attribute_id = 45 AND store_id = 0";
        $this->categoryList = $connection->fetchAll($queryList);
        $catIds = array_column($this->categoryList, 'entity_id');
        $final_data = [[
            'id' => 'id',
            'title' => 'title',
            'description' => 'description',
            'brand' => 'brand',
            'color' => 'color',
            'price' => 'price',
            'sale_price' => 'sale_price',
            'link' => 'link',
            'image_link' => 'image_link',
            'visibility' => 'visibility',
            'google_product_category' => 'google_product_category',
            'availability' => 'availability',
            'custom_label_1' => 'custom_label_1',
            'sale_price_effective_date' => 'sale_price_effective_date',
            'identifier_exists' => 'identifier_exists',
            'condition' => 'condition',
            'mobile_link' => 'mobile_link',
            'custom_label_0' => 'custom_label_0',
            'custom_label_2' => 'custom_label_2',
            'custom_label_3' => 'custom_label_3'
        ]];
        foreach ($products as $product) {
            $product['visibility'] = 'published';
            if ($this->checkStockStatus($product['id'], $product, $connection) === false) {
                $product['visibility'] = 'hidden';
                continue;
            }
            $categories = explode(',', $product['categories']);
            asort($categories);
            foreach ($categories as $category) {
                $key = array_search($category, $catIds);
                $path = $this->categoryList[$key]['path'];
                $name = $this->categoryList[$key]['name'];
                $product['google_product_category'] = $this->getParentName($path, $catIds) . $name;
            }

            if (trim(strip_tags($product['description'])) == "") {
                $description = $this->limitText($this->cleanText($product['title']), 140);
            } elseif(trim(strip_tags($product['description'])) != "" && !empty(trim(strip_tags($product['description'])))) {
                $description = $this->limitText($this->cleanText($product['description']), 140);
            } else {
                $description = 'Item Description';
            }

            $product['availability'] = 'in stock';
            $product['title'] = $this->limitText($this->cleanText($product['title']), 140);
            $product['description'] = $description;
            $product['custom_label_1'] = is_numeric($product['sale_price']) ? $this->calculateDiscount($product['sale_price'], $product['price']) : '0%';
            $product['sale_price'] = $product['sale_price'] ? $product['sale_price'] . " " . $currency : '';
            $product['price'] = $product['price'] . " " . $currency;
            $product['sale_price_effective_date'] = $this->salesPriceEffectiveDate($product['sale_price_from_date'], $product['sale_price_to_date']);//!empty($product['sale_price_effective_date']) ? $this->dateFormat($product['sale_price_effective_date']) : null;
            $product['identifier_exists'] = 'false';
            $product['condition'] = 'New';
            $product['brand'] = !empty($product['brand']) ? $product['brand'] : 'Tamata';
            $product['mobile_link'] = 'fbtamata://products/?productid='.$product['entity_id'];
            $product['custom_label_0'] = $product['entity_id'];
            $product['custom_label_2'] = $product['vendor_name'];
            $product['custom_label_3'] = $this->getSalesPriceIfValid($product['price'], $product['sale_price'], $product['sale_price_from_date'], $product['sale_price_to_date']);
            unset($product['categories']);
            unset($product['entity_id']);
            unset($product['vendor_name']);
            unset($product['sale_price_from_date']);
            unset($product['sale_price_to_date']);
            unset($product['quantity']);
            $final_data[] = $product;
        }
        return $final_data;
    }

    /**
     * @param $storeId
     * @return false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCurrency($storeId)
    {
        $currencyCode = $this->storeManager->getStore($storeId)->getCurrentCurrencyCode();
        $symbol = $this->currencyFactory->create()->load($currencyCode)->getCurrencySymbol();

        if ($symbol) {
            return $symbol;
        }
        return false;
    }

    /**
     * @param $sku
     * @param $product
     * @param $connection
     * @return bool
     */
    public function checkStockStatus($sku, $product, $connection)
    {
        /*$query = "SELECT
            CASE
                WHEN cisi.is_in_stock = 1 THEN 1
                ELSE 0
            END as status FROM catalog_product_entity AS cpe
            LEFT JOIN cataloginventory_stock_item AS cisi ON cisi.product_id = cpe.entity_id
            WHERE sku = '" . $sku . "'";
        $childStatus = $connection->fetchCol($query);
        if (array_sum($childStatus) > 0) {
            return true;
        }
        return false;*/
        if (!empty($product['quantity']) && $product['quantity'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $path
     * @param $catIds
     * @return string
     */
    public function getParentName($path, $catIds)
    {
        $parentName = '';
        $rootCats = [1,2];
        $catTree = explode("/", $path);
        array_pop($catTree);

        if ($catTree && (count($catTree) > count($rootCats))) {
            foreach ($catTree as $catId) {
                if (!in_array($catId, $rootCats)) {
                    $key = array_search($catId, $catIds);
                    $parentName .= $this->categoryList[$key]['name'] . ' > ';
                }
            }
        }
        return $parentName;
    }

    /**
     * @param $specialPrice
     * @param $productPrice
     * @return string
     */
    protected function calculateDiscount($specialPrice, $productPrice)
    {
        if(is_numeric($productPrice) && $productPrice > 0) {
            $discount = $productPrice - $specialPrice;
            return round(($discount / $productPrice) * 100) . "%";
        }
        return '0%';
    }

    /**
     * @param $array
     * @param $fileName
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createFeeds($array, $fileName)
    {
        $filePath = 'cat/feed/fb/';
        $target = $this->mediaDirectory->getAbsolutePath($filePath);
        $this->file->createDirectory($target);
        if ($this->file->isExists($target . $fileName)) {
            $this->file->deleteFile($target . $fileName);
        }
        $fileHandler = fopen($target . $fileName, 'w');
        foreach ($array as $item) {
            $this->file->filePutCsv($fileHandler, $item);
        }
        fclose($fileHandler);
    }

    /**
     * @return \Zend\Log\Logger
     */
    public function  getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fbFeed.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }

    /**
     * @param null $date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function dateFormat($date = null, $format = 'Y-m-d\TH:iP')
    {
        return $this->timezone->date(new \DateTime($date))->format($format);
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return string|null
     * @throws \Exception
     */
    public function salesPriceEffectiveDate($fromDate, $toDate) {
        if(!empty($fromDate)) {
            $fromDate = $this->dateFormat($fromDate);
            if (empty($toDate)) {
                $effectiveDate = strtotime("+6 months", strtotime(date("Y-m-d H:i:s")));
                $newDate = date("Y-m-d H:i:s", $effectiveDate);
                $toDate = $this->dateFormat($newDate);
            }
            $toDate = $this->dateFormat($toDate);
            $finalDate = $fromDate.'/'.$toDate;
            return $finalDate;
        }
        return null;
    }

    /**
     * @param $price
     * @param $salesPrice
     * @param $salesPriceFromDate
     * @param $salesPriceToDate
     * @return mixed
     */
    public function getSalesPriceIfValid($price, $salesPrice, $salesPriceFromDate, $salesPriceToDate) {
        if (!$salesPrice) {
            return $price;
        }
        if ($salesPrice && !$salesPriceFromDate) {
            return $salesPrice;
        }
        if ($salesPrice && $salesPriceFromDate && !$salesPriceToDate) {
            return $salesPrice;
        } else {
            $Date1 = strtotime(date('Y-m-d H:i:s', strtotime($salesPriceToDate)));
            $Date2 = strtotime(date('Y-m-d H:i:s'));
            if ($Date1 > $Date2) {
                return $salesPrice;
            }
            return $price;
        }
    }

    /**
     * @param $str
     * @return string
     */
    public function cleanText($str) {
        return ucwords(
            mb_strtolower(
                trim(
                    html_entity_decode(
                        str_replace( "&nbsp;", "", htmlentities(
                            strip_tags(
                                    $str
                                ), null, 'utf-8'
                            )
                        ), ENT_HTML5 | ENT_QUOTES, 'UTF-8'
                    )
                )
            )
        );
    }

    /**
     * @param $string
     * @param $limit
     * @return false|string
     */
    public function limitText($string, $limit)
    {
        if (function_exists('mb_substr')) {
            $str = mb_substr($string, 0, $limit, 'UTF-8');
        } else {
            $str = substr($string, 0, $limit);
        }

        return $str;
    }
}
