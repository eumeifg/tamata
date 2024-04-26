<?php

namespace MDC\Shippingmatrix\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;


class Matrixrate extends \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_resourceConnection;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $carrierMatrixrate
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ResourceConnection $resourceConnection
     * @param $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context                $context,
        \Psr\Log\LoggerInterface                                         $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface               $coreConfig,
        \Magento\Store\Model\StoreManagerInterface                       $storeManager,
        \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate             $carrierMatrixrate,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory  $regionCollectionFactory,
        \Magento\Framework\Filesystem                                    $filesystem,
        \Magento\Quote\Api\CartRepositoryInterface                       $quoteRepository,
        ResourceConnection                                               $resourceConnection,
                                                                         $resourcePrefix = null
    )
    {
        $this->_resourceConnection = $resourceConnection->getConnection();
        parent::__construct(
            $context,
            $logger,
            $coreConfig,
            $storeManager,
            $carrierMatrixrate,
            $countryCollectionFactory,
            $regionCollectionFactory,
            $filesystem,
            $quoteRepository,
            $resourcePrefix
        );
    }

    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $zipRangeSet = false)
    {
        /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/shipping-method-amount.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);*/

        $customerGroupId = $request->getCustomerGroupId();
        $requestQuoteId = 0;
        foreach ($request->getAllItems() as $item) {
            $requestQuoteId = $item->getQuoteId();
            break;
        }

        $addressTypeSql = $this->_resourceConnection->select()
            ->from('quote', 'address_type')
            ->where('entity_id=?', $requestQuoteId);
        $customerCityType = $this->_resourceConnection->fetchOne($addressTypeSql); //Is province city, 1= Yes, 0= No

        $adapter = $this->getConnection();
        $shippingData = [];
        $postcode = $request->getDestPostcode();
        $city = $request->getDestCity();

        /*$logger->info(__('Locale %1',$locale));*/

        $select = $this->_resourceConnection->select()
            ->from('address_city', 'city')
            ->where('city_ar =? ', $city)
            ->orWhere('city =? ', $city);
        /*$logger->info($select);*/
        $city = $this->_resourceConnection->fetchOne($select);
        /*$logger->info(__('city: %1',$city));*/
        $city = !empty($city) ? $city : '*';

        if ($zipRangeSet && is_numeric($postcode)) {
            #  Want to search for postcodes within a range
            $zipSearchString = ' AND :postcode BETWEEN dest_zip AND dest_zip_to ';
        } else {
            $zipSearchString = " AND :postcode LIKE dest_zip ";
        }

        for ($j = 0; $j < 4; $j++) {
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
                case 0: // no vendor_id, no country, no state, city, no postcode, customer_group_id
                    $zoneWhere = "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city AND dest_zip='*' AND customer_group_id = :customer_group_id";
                    $bind = [
                        ':city' => $city,
                        ':province_city' => $customerCityType,
                        ':customer_group_id' => $customerGroupId,
                    ];
                    break;
                case 1: // no vendor_id, no country, no state, city, no postcode, no customer_group_id
                    $zoneWhere = "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city AND dest_zip='*'";
                    $bind = [
                        ':city' => $city,
                        ':province_city' => $customerCityType,
                    ];
                    break;
                case 2: // no vendor_id, no country, no state, no city, no postcode, customer_group_id
                    $zoneWhere = "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = '*' AND dest_zip='*' AND customer_group_id = :customer_group_id";
                    $bind = [
                        ':customer_group_id' => $customerGroupId,
                    ];
                    break;
                case 3: // no vendor_id, no country, no state, no city, no postcode, no customer_group_id
                    $zoneWhere = "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city ='*' AND dest_zip ='*' AND customer_group_id = 0";
                    break;


                /*case 0: // vendor_id, country, region, city, postcode, no customer_group
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = :city AND province_city = :province_city ".$zipSearchString." AND customer_group_id = 0";  // TODO Add city
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 1: // vendor_id, country, region, no city, postcode, no customer_group
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = '*' ".$zipSearchString." AND customer_group_id = 0";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':vendor_id' => $request->getVendorId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;*/
                /*case 2: // vendor_id, country, no region, city, postcode, no customer_group
                    $zoneWhere = "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString." AND customer_group_id = 0";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':vendor_id' => $request->getVendorId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 3: // vendor_id, country, state, city, no postcode, no customer_group
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = :city AND province_city = :province_city AND dest_zip='*' AND customer_group_id = 0"; // TODO Add city search
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                        ':vendor_id' => $request->getVendorId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 4: // no vendor_id, country, state, city, postcode, no customer_group
                    $zoneWhere = "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString." AND customer_group_id = 0";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 5: // vendor_id, country, state, no city, no postcode, no customer_group
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = '*' AND dest_zip='*' AND customer_group_id = 0";
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                    ];
                    break;*/
                /*case 6: // vendor_id, country, no state, no city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = '*' ".$zipSearchString;
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;*/
                /*case 7: // no vendor_id, country, state, no city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = '*' ".$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;*/
                /*case 8: // vendor_id, country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city AND dest_zip = '*' ";
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 9: // no vendor_id, country, state, city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = :city AND province_city = :province_city AND dest_zip='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 10: // vendor_id, no country, no state, city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':city' => $request->getDestCity(),
                        ':vendor_id' => $request->getVendorId(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 11: // no vendor_id, country, no state, city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':country_id' => $request->getDestCountryId(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 12: // vendor_id, no country, no state, no city, postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city='*'".$zipSearchString;
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;*/
                /*case 13: // vendor_id, country, no state, no city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                        ':vendor_id' => $request->getVendorId(),
                    ];
                    break;*/
                /*case 14: // no vendor_id, country, state, no city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':region_id' => (int)$request->getDestRegionId(),
                        ':country_id' => $request->getDestCountryId(),
                    ];
                    break;*/
                /*case 15: // vendor_id, no country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ";
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':vendor_id' => $request->getVendorId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 16: // no vendor_id, country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ";
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':country_id' => $request->getDestCountryId(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 17: // no vendor_id, no country, no state, city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city ".$zipSearchString;
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':postcode' => $request->getDestPostcode(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 18: // vendor_id, no country, no state, no city, no postcode
                    $zoneWhere =  "vendor_id = :vendor_id AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':vendor_id' => $request->getVendorId(),
                    ];
                    break;*/
                /*case 19: // no vendor_id, country, no state, no city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = :country_id AND dest_region_id = '0' AND dest_city='*' AND dest_zip='*'";
                    $bind = [
                        ':country_id' => $request->getDestCountryId(),
                    ];
                    break;*/
                /*case 20: // no vendor_id, no country, no state, city, no postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city = :city AND province_city = :province_city AND dest_zip='*'";
                    $bind = [
                        ':city' => $request->getDestCity(),
                        ':province_city' => $customerCityType,
                    ];
                    break;*/
                /*case 21: // no vendor_id, no country, no state, no city, postcode
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city='*'".$zipSearchString;
                    $bind = [
                        ':postcode' => $request->getDestPostcode(),
                    ];
                    break;*/
                /*case 22: // no vendor_id, no country, no state, no city, no postcode. no customer_group_id
                    $zoneWhere =  "vendor_id = '0' AND dest_country_id = '0' AND dest_region_id = '0' AND dest_city ='*' AND dest_zip ='*' AND customer_group_id = 0";
                    break;*/
            }

            $select->where($zoneWhere);

            $bind[':website_id'] = (int)$request->getWebsiteId();
            $bind[':condition_name'] = $request->getConditionMRName();
            $bind[':condition_value'] = $request->getData($request->getConditionMRName());

            $select->where('condition_name = :condition_name');
            $select->where('condition_from_value <= :condition_value');
            $select->where('condition_to_value >= :condition_value');

            /*$logger->info('\n-------------------------------------------\n');
            $logger->info('WHERE:'.$zoneWhere);
            $logger->info('\n-------------------------------------------\n');
            $logger->info('J:'.$j);
            $logger->info('Bind'.json_encode($bind));
            $logger->info('\n____________________________________________\n');
            $logger->info('Select:'.$select);
            $logger->info('\n-------------------------------------------\n');*/


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
        /*$logger->info('ShippingData'.json_encode($shippingData));
        $logger->info('\n-------------------------------------------\n');*/
        return $shippingData;
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate
     * @throws \Magento\Framework\Exception\LocalizedException
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

        // validate condition to value
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

        //customer_group_id
        $customerGroupId = $row[11];

        // protect from duplicate
        $hash = sprintf("%s-%s-%s-%s-%F-%F-%s-%s-%s-%d", $countryId, $city, $regionId, $zipCode, $valueFrom, $valueTo, $shippingMethod, $row[9], $provinceCity, $customerGroupId);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = __(
                'Duplicate Row #%1 (Country "%2", Region/State "%3", City "%4", Zip from "%5", Zip to "%6", From Value "%7", To Value "%8", Shipping Method "%9", Vendor Id "%10", Province city "%11", Customer Group Id "%11")',
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
                $row[10],
                $row[11]
            );
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return [
            $this->_importWebsiteId,        // website_id
            $countryId,                     // dest_country_id
            $regionId,                      // dest_region_id,
            $city,                          // city,
            $zipCode,                       // dest_zip
            $zipTo,                         //zip to
            $this->_importConditionName,    // condition_name,
            $valueFrom,                     // condition_value From
            $valueTo,                       // condition_value To
            $price,                         // price
            $shippingMethod,                // shipping_method
            $row[9],                        // vendor id,
            $row[10],                       // province_city
            $row[11],                       // customer_group_id
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
                'province_city',
                'customer_group_id'
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }
        return $this;
    }

}
