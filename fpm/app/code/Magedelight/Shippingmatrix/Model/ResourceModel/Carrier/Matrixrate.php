<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Matrixrate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = [];

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;

    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $_importConditionName;

    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $_conditionFullNames = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate
     */
    protected $_carrierMatrixrate;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $carrierMatrixrate
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $carrierMatrixrate,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_coreConfig = $coreConfig;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->_carrierMatrixrate = $carrierMatrixrate;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->_filesystem = $filesystem;
    }

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('md_shipping_matrixrate', 'pk');
    }

    /**
     * Return table rate array or false by rate request
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param bool $zipRangeSet
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $zipRangeSet = false)
    {
       // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/matrixcolletion.log');
       // $logger = new \Zend\Log\Logger();
       // $logger->addWriter($writer);
       // $logger->info('Request:'.json_encode($request->getQuoteId()));
       //  $logger->info('\n-------------------------------------------\n');
       // $logger->info( $request->getDestCity());
       // $logger->info('\n-------------------------------------------\n');

        $requestQuoteId = 0;
        foreach ($request->getAllItems() as $item) {
            $requestQuoteId = $item->getQuoteId();
        }

        $requestAddressQuote = $this->quoteRepository->getActive($requestQuoteId);

        $customerCityType = $requestAddressQuote->getData("address_type");  //Is province city, 1= Yes, 0= No


        $adapter = $this->getConnection();
        $shippingData=[];
        $postcode = $request->getDestPostcode();
        $city = $request->getDestCity();
        if ($zipRangeSet && is_numeric($postcode)) {
            #  Want to search for postcodes within a range
            $zipSearchString = ' AND :postcode BETWEEN dest_zip AND dest_zip_to ';
        } else {
            $zipSearchString = " AND :postcode LIKE dest_zip ";
        }

        for ($j=0; $j<23; $j++) {
            $select = $adapter->select()->from(
                $this->getMainTable()
            )->where(
                'website_id = :website_id'
            )->order(
                ['price ASC', 'condition_from_value DESC']
            );

            $zoneWhere = '';
            $bind = [];
            switch ($j) {
                case 0: // vendor_id, country, region, city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = :city AND province_city = :province_city ".$zipSearchString;  // TODO Add city
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 1: // vendor_id, country, region, no city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = '*' ".$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':vendor_id' => $request->getVendorId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;
                case 2: // vendor_id, country, no region, city, postcode
                    $zoneWhere = "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':vendor_id' => $request->getVendorId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 3: // vendor_id, country, state, city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = :city AND province_city = :province_city AND dest_zip='*'"; // TODO Add city search
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                        ':vendor_id' => $request->getVendorId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 4: // no vendor_id, country, state, city, postcode
                    $zoneWhere = "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 5: // vendor_id, country, state, no city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = '*' AND dest_zip='*'";
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                    ];
                    break;
                case 6: // vendor_id, country, no state, no city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = '*' ".$zipSearchString;
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;
                case 7: // no vendor_id, country, state, no city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = '*' ".$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;
                case 8: // vendor_id, country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city AND dest_zip = '*' ";
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 9: // no vendor_id, country, state, city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = :city AND province_city = :province_city AND dest_zip='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 10: // vendor_id, no country, no state, city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':vendor_id' => $request->getVendorId(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 11: // no vendor_id, country, no state, city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':country_id' => $request->getDestCountryId(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 12: // vendor_id, no country, no state, no city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city='*'".$zipSearchString;
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;
                case 13: // vendor_id, country, no state, no city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':vendor_id' => $request->getVendorId(),
                    ];
                    break;
                case 14: // no vendor_id, country, state, no city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':country_id' => $request->getDestCountryId(),
                    ];
                    break;
                case 15: // vendor_id, no country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ";
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':vendor_id' => $request->getVendorId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 16: // no vendor_id, country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ";
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':country_id' => $request->getDestCountryId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 17: // no vendor_id, no country, no state, city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 18: // vendor_id, no country, no state, no city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                    ];
                    break;
                case 19: // no vendor_id, country, no state, no city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                    ];
                    break;
                case 20: // no vendor_id, no country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city AND dest_zip='*'";
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 21: // no vendor_id, no country, no state, no city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city='*'".$zipSearchString;
                    $bind = [
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;
                case 22: // no vendor_id, no country, no state, no city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city ='*' AND dest_zip ='*'";
                    break;
            }

            $select->where($zoneWhere);

            $bind[':website_id'] = (int)$request->getWebsiteId();
            $bind[':condition_name'] = $request->getConditionMRName();
            $bind[':condition_value'] = $request->getData($request->getConditionMRName());
            
            $select->where('condition_name = :condition_name');
            $select->where('condition_from_value <= :condition_value');
            $select->where('condition_to_value >= :condition_value');            

           // $logger->info('\n-------------------------------------------\n');
           // $logger->info('WHERE:'.$zoneWhere);
           // $logger->info('\n-------------------------------------------\n');
           // $logger->info('J:'.$j);
           // $logger->info('Bind'.json_encode($bind));
           // $logger->info('\n____________________________________________\n');
           // $logger->info('Select:'.$select);        
           // $logger->info('\n-------------------------------------------\n');

            $results = $adapter->fetchAll($select, $bind);


            if (!empty($results)) {
                foreach ($results as $data) {
                    if (!array_key_exists($data['shipping_method'], $shippingData)) {
                        $shippingData[$data['shipping_method']] = $data;
                    }
                }
                break;
            }
        }
       // $logger->info('ShippingData'.json_encode($shippingData));
       // $logger->info('\n-------------------------------------------\n');
        return $shippingData;
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param \Magento\Framework\Object $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate
     * @todo: this method should be refactored as soon as updated design will be provided
     * @see https://wiki.corp.x.com/display/MCOMS/Magento+Filesystem+Decisions
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        if (empty($_FILES['groups']['tmp_name']['rbmatrixrate']['fields']['import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['rbmatrixrate']['fields']['import']['value'];
        $website = $this->_storeManager->getWebsite($object->getScopeId());

        $this->_importWebsiteId = (int)$website->getId();
        $this->_importUniqueHash = [];
        $this->_importErrors = [];
        $this->_importedRows = 0;

        $tmpDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($csvFile);
        $stream = $tmpDirectory->openFile($path);

        // check and skip headers
        $headers = $stream->readCsv();
        if ($headers === false || count($headers) < 5) {
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct Matrix Rates File Format.'));
        }

        if ($object->getData('groups/rbmatrixrate/fields/condition_name/inherit') == '1') {
            $conditionName = (string)$this->_coreConfig->getValue('carriers/rbmatrixrate/condition_name', 'default');
        } else {
            $conditionName = $object->getData('groups/rbmatrixrate/fields/condition_name/value');
        }
        $this->_importConditionName = $conditionName;

        $adapter = $this->getConnection();
        $adapter->beginTransaction();

        try {
            $rowNumber = 1;
            $importData = [];

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website and condition name
            $condition = [
                'website_id = ?' => $this->_importWebsiteId,
                'condition_name = ?' => $this->_importConditionName,
            ];
            $adapter->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $stream->readCsv())) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = [];
                }
            }
            $this->_saveImportData($importData);
            $stream->close();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $adapter->rollback();
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            $adapter->rollback();
            $stream->close();
            $this->_logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing matrix rates.')
            );
        }

        $adapter->commit();

        if ($this->_importErrors) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->_importErrors)
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }

        return $this;
    }

    /**
     * Load directory countries
     *
     * @return \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate
     */
    protected function _loadDirectoryCountries()
    {
        if ($this->_importIso2Countries !== null && $this->_importIso3Countries !== null) {
            return $this;
        }

        $this->_importIso2Countries = [];
        $this->_importIso3Countries = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Country\Collection */
        $collection = $this->_countryCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->_importIso2Countries[$row['iso2_code']] = $row['country_id'];
            $this->_importIso3Countries[$row['iso3_code']] = $row['country_id'];
        }

        return $this;
    }

    /**
     * Load directory regions
     *
     * @return \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate
     */
    protected function _loadDirectoryRegions()
    {
        if ($this->_importRegions !== null) {
            return $this;
        }

        $this->_importRegions = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */
        $collection = $this->_regionCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->_importRegions[$row['country_id']][$row['code']] = (int)$row['region_id'];
        }

        return $this;
    }

    /**
     * Return import condition full name by condition name code
     *
     * @param string $conditionName
     * @return string
     */
    protected function _getConditionFullName($conditionName)
    {
        if (!isset($this->_conditionFullNames[$conditionName])) {
            $name = $this->_carrierMatrixrate->getCode('condition_name_short', $conditionName);
            $this->_conditionFullNames[$conditionName] = $name;
        }

        return $this->_conditionFullNames[$conditionName];
    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 7) {
            $this->_importErrors[] = __('Please correct Matrix Rates format in Row #%1. Invalid Number of Rows', $rowNumber);
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        // validate country
        if (isset($this->_importIso2Countries[$row[0]])) {
            $countryId = $this->_importIso2Countries[$row[0]];
        } elseif (isset($this->_importIso3Countries[$row[0]])) {
            $countryId = $this->_importIso3Countries[$row[0]];
        } elseif ($row[0] == '*' || $row[0] == '') {
            $countryId = '0';
        } else {
            $this->_importErrors[] = __('Please correct Country "%1" in Row #%2.', $row[0], $rowNumber);
            return false;
        }

        // validate region
        if ($countryId != '0' && isset($this->_importRegions[$countryId][$row[1]])) {
            $regionId = $this->_importRegions[$countryId][$row[1]];
        } elseif ($row[1] == '*' || $row[1] == '') {
            $regionId = 0;
        } else {
            $this->_importErrors[] = __('Please correct Region/State "%1" in Row #%2.', $row[1], $rowNumber);
            return false;
        }

        // detect city
        if ($row[2] == '*' || $row[2] == '') {
            $city = '*';
        } else {
            $city = $row[2];
        }

        // detect zip code
        if ($row[3] == '*' || $row[3] == '') {
            $zipCode = '*';
        } else {
            $zipCode = $row[3];
        }

        //zip from
        if ($row[4] == '*' || $row[4] == '') {
            $zipTo = '*';
        } else {
            $zipTo = $row[4];
        }

        // validate condition from value
        $valueFrom = $this->_parseDecimalValue($row[5]);
        if ($valueFrom === false) {
            $this->_importErrors[] = __(
                'Please correct %1 From "%2" in Row #%3.',
                $this->_getConditionFullName($this->_importConditionName),
                $row[5],
                $rowNumber
            );
            return false;
        }

        // validate conditionto to value
        $valueTo = $this->_parseDecimalValue($row[6]);
        if ($valueTo === false) {
            $this->_importErrors[] = __(
                'Please correct %1 To "%2" in Row #%3.',
                $this->_getConditionFullName($this->_importConditionName),
                $row[6],
                $rowNumber
            );
            return false;
        }

        // validate price
        $price = $this->_parseDecimalValue($row[7]);
        if ($price === false) {
            $this->_importErrors[] = __('Please correct Shipping Price "%1" in Row #%2.', $row[7], $rowNumber);
            return false;
        }

        // validate shipping method
        if ($row[8] == '*' || $row[8] == '') {
            $this->_importErrors[] = __('Please correct Shipping Method "%1" in Row #%2.', $row[8], $rowNumber);
            return false;
        } else {
            $shippingMethod = $row[8];
        }

        //province_city
        $provinceCity = $row[10];

        // protect from duplicate
        $hash = sprintf("%s-%d-%s-%s-%F-%F-%s-%s", $countryId, $city, $regionId, $zipCode, $valueFrom, $valueTo, $shippingMethod, $row[9], $provinceCity);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = __(
                'Duplicate Row #%1 (Country "%2", Region/State "%3", City "%4", Zip from "%5", Zip to "%6", From Value "%7", To Value "%8", Shipping Method "%9", and Vendor Id "%10")',
                $rowNumber,
                $row[0],
                $row[1],
                $city,
                $zipCode,
                $zipTo,
                $valueFrom,
                $valueTo,
                $shippingMethod,
                $row[9],
                $row[10]
            );
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return [
            $this->_importWebsiteId,    // website_id
            $countryId,                 // dest_country_id
            $regionId,                  // dest_region_id,
            $city,                      // city,
            $zipCode,                   // dest_zip
            $zipTo,                    //zip to
            $this->_importConditionName,// condition_name,
            $valueFrom,                 // condition_value From
            $valueTo,                   // condition_value To
            $price,                     // price
            $shippingMethod,
            $row[9],                     // vendor id,
            $row[10]                     // province_city
        ];
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = [
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_city',
                'dest_zip',
                'dest_zip_to',
                'condition_name',
                'condition_from_value',
                'condition_to_value',
                'price',
                'shipping_method',
                'vendor_id',
                'province_city'
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }
        return $this;
    }

    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     * @return bool|float
     */
    protected function _parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (double)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }
}
