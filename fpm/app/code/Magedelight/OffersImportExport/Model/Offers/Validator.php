<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OffersImportExport\Model\Offers;

use Magento\Catalog\Model\Product\Type;

class Validator extends \Magento\Framework\DataObject
{
    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * @var \Magedelight\OffersImportExport\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepositoryInterface;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * Validator constructor.
     * @param \Magedelight\OffersImportExport\Helper\Data $helper
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceModel
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepositoryInterface
     */
    public function __construct(
        \Magedelight\OffersImportExport\Helper\Data $helper,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepositoryInterface
    ) {
        $this->helper = $helper;
        $this->csvProcessor = $csvProcessor;
        $this->vendorRepositoryInterface = $vendorRepositoryInterface;
        $this->resourceModel = $resourceModel;
        $this->vendorProductResource = $vendorProductResource;
    }

    /**
     * @param $file
     * @param string $type
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function execute($file, $type = 'new', $request)
    {
        $errors = [];
        try {
            if (isset($file)) {
                $importProductRawData = $this->csvProcessor->getData($file['tmp_name']);
                $headers = $importProductRawData[0];
                unset($importProductRawData[0]);
                $predefinedHeaders = array_keys($this->helper->getCSVFields());

                if ($headers === false) {
                    $errors['invalid_file_format'] =__('Please correct offers file format.');
                }

                if (array_diff($predefinedHeaders, $headers)) {
                    $errors['invalid_file_columns'] =__('Sample file and Uploading file\'s header fields are not matched.');
                }
                $fields = $predefinedHeaders;
                $errors = [];
                foreach ($importProductRawData as $rowIndex => $dataRow) {
                    $rowData = [];
                    foreach ($fields as $key => $field) {
                        if (in_array($field, ['status'])) {
                            $rowData[$field] = (isset($dataRow[$key])) ? $dataRow[$key] : null;
                        } else {
                            $rowData[$field] = (!empty($dataRow[$key])) ? trim($dataRow[$key]) : null;
                        }
                    }
                    $rowErrors = $this->validateRow($rowData, $rowIndex+1, $predefinedHeaders, $type, $request);
                    if (!empty($rowErrors)) {
                        $errors[$rowIndex] = implode(" \n", $rowErrors);
                    }
                }
                return $errors;
            } else {
                $errors['invalid_file_format'] =__('Invalid file upload attempt.');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $errors;
    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @param $predefinedHeaders
     * @param $type
     * @param $request
     * @return array|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function validateRow(
        $row,
        $rowNumber = 0,
        $predefinedHeaders,
        $type,
        $request
    ) {
        $numOfColumnsInRow = count($row);
        $columnsLimit = count($predefinedHeaders);

        /* validate row */
        if ($numOfColumnsInRow < $columnsLimit) {
            $this->_importErrors[] = __('Please correct offers csv format in row #%1. Invalid number of columns', $rowNumber);
        }

        /* strip whitespace from the beginning and end of each row */
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        /* validate marketplace_sku */
        if (empty($row['marketplace_sku'])) {
            $this->_importErrors[] = __('Please correct marketplace_sku "%1" in row #%2.', $row['marketplace_sku'], $rowNumber);
        } else {
            try {
                $productType = $this->getProductTypeBySku($row['marketplace_sku']);
                if ($productType) {
                    if ($productType != Type::TYPE_SIMPLE) {
                        $this->_importErrors[] = __('Please make sure marketplace_sku %1 is of simple product in row #%2.', $row['marketplace_sku'], $rowNumber);
                    }
                } else {
                    $this->_importErrors[] = __('No Such marketplace_sku found "%1" in row #%2.', $row['marketplace_sku'], $rowNumber);
                }
            } catch (\Exception $exc) {
                $this->_importErrors[] = __('Please correct marketplace_sku "%1" in row #%2.', $row['marketplace_sku'], $rowNumber);
            }
        }

        /* validate vendor Id */
        if (!empty($row['vendor_id'])) {
            try {
                $vendor = $this->vendorRepositoryInterface->getById($row['vendor_id']);
                if (!$vendor->getId()) {
                    $this->_importErrors[] = __(
                        'Please correct vendor_id value %1 in Row #%2.',
                        $row['vendor_id'],
                        $rowNumber
                    );
                }
                if ($request->getParam('vendor_id') != $row['vendor_id']) {
                    $this->_importErrors[] = __('Vendor Id does not match with selected vendor in row #%1.', $rowNumber);
                }

                if ($type == 'edit') {
                    $vendorProduct = $this->isVendorProductExists($row['vendor_sku']);
                    if (!$vendorProduct) {
                        $this->_importErrors[] = __('Vendor product does not exist for vendor_sku "%1" in row #%2.', $row['vendor_sku'], $rowNumber);
                    }
                }
            } catch (\Exception $e) {
                $this->_importErrors[] = __(
                    'Please correct vendor_id value %1 in Row #%2.',
                    $row['vendor_id'],
                    $rowNumber
                );
            }
        } else {
            $this->_importErrors[] = __('Please correct vendor_id "%1" in row #%2.', $row['vendor_id'], $rowNumber);
        }

        /* validate price */
        $valueTo = $this->_parseDecimalValue((int)$row['price']);
        if ($valueTo === false) {
            $this->_importErrors[] = __(
                'Please correct price  %1 in row #%2.',
                $row['price'],
                $rowNumber
            );
        }

        /* validate special_price */
        $special_priceValueTo = $this->_parseDecimalValue((int)$row['special_price']);
        if ($special_priceValueTo === false) {
            $this->_importErrors[] = __(
                'Please correct special_price   %1 in row #%2.',
                $row['special_price'],
                $rowNumber
            );
        }
        return $this->_importErrors;
    }

    /**
     * Get product identifier by sku
     *
     * @param string $sku
     * @return int|false
     */
    public function getProductTypeBySku($sku)
    {
        $connection = $this->resourceModel->getConnection();

        $select = $connection->select()->from($this->resourceModel->getEntityTable(), ['type_id'])->where('sku = :sku');

        $bind = [':sku' => (string)$sku];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Get product identifier by sku
     *
     * @param $vendorSku
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isVendorProductExists($vendorSku)
    {
        $connection = $this->resourceModel->getConnection();

        $select = $connection->select()->from($this->vendorProductResource->getMainTable(), ['vendor_product_id'])
            ->where('vendor_sku = :vendor_sku');

        $bind = [':vendor_sku' => (string)$vendorSku];

        return $connection->fetchOne($select, $bind);
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
