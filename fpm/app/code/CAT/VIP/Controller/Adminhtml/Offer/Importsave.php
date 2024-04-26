<?php

namespace CAT\VIP\Controller\Adminhtml\Offer;

use Magedelight\Catalog\Model\Product;
use Magedelight\Catalog\Model\ProductRequestManagement;

class Importsave extends \Magento\Backend\App\Action
{
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;
    const CORE_PRODUCT_TYPE_DEFAULT = 'simple';
    const CORE_PRODUCT_TYPE_ASSOCIATED = 'config-simple';
    protected $_importErrors = [];

    protected $csvProcessor;

    protected $vendorProductFactory;

    protected $productRepository;

    protected $mathRandom;

    protected $vendorRepositoryInterface;

    protected $offersValidator;
    protected $productModel;
    protected $vipProductsFactory;
    protected $helper;

    public $feilds = [
            "product_id"=>"product_id",
            "vendor_id"=>"vendor_id",
            "ind_qty"=>"ind_qty",
            "global_qty"=>"global_qty",
            "type"=>"type",
            "discount"=>"discount",
            "customer_group"=>"customer_group",
        ];

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \CAT\VIP\Helper\UpdatesData $helper,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\Product $productModel,
        \CAT\VIP\Model\VIPProductsFactory $vipProductsFactory,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepositoryInterface
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->helper = $helper;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->vendorRepositoryInterface = $vendorRepositoryInterface;
        $this->productModel = $productModel;
        $this->vipProductsFactory = $vipProductsFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $_filesParam = $this->getRequest()->getFiles()->toArray();

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if (isset($_filesParam['vip_offers'])) {
                $csvFile = $_filesParam['vip_offers'];
                $errors = $this->validateData($csvFile, $this->getRequest());
                if (count($errors) > 0) {
                    $this->messageManager->addErrorMessage(__('Validation Results %1', implode(" \n", $errors)));
                } else {
                    $result = $this->importFromCsvFile($csvFile);
                    if ($result) {
                        if ($result['updatedDataCount'] > 0) {
                            $this->messageManager->addSuccessMessage(__('%1 Offer(s) updated.', $result['updatedDataCount']));
                        }
                        if ($result['newDataCount'] > 0) {
                            $this->messageManager->addSuccessMessage(__('%1 Offer(s) saved.', $result['newDataCount']));
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
        return $resultRedirect->setPath('viporders/offer/import');
    }

    Protected function validateData($file,$request){
        $errors = [];
        try {
            if (isset($file)) {
                $importProductRawData = $this->csvProcessor->getData($file['tmp_name']);
                $headers = $importProductRawData[0];
                unset($importProductRawData[0]);
                $predefinedHeaders = array_keys($this->feilds);
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
                    $rowErrors = $this->validateRow($rowData, $rowIndex+1, $predefinedHeaders, $request);
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

    protected function validateRow(
        $row,
        $rowNumber = 0,
        $predefinedHeaders,
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

        /* validate Product Id */
        if (!empty($row['product_id'])) {
            try {
                $product = $this->productModel->load($row['product_id']);
                if (!$product->getId()) {
                    $this->_importErrors[] = __(
                        'Please correct product_id value %1 in Row #%2.',
                        $row['product_id'],
                        $rowNumber
                    );
                }

            } catch (\Exception $e) {
                $this->_importErrors[] = __(
                    'Please correct product_id value %1 in Row #%2.',
                    $row['product_id'],
                    $rowNumber
                );
            }
        } else {
            $this->_importErrors[] = __('Please correct product_id "%1" in row #%2.', $row['product_id'], $rowNumber);
        }

        /* validate ind_qty */
        $valueTo = (int)$row['ind_qty'];
        if ($valueTo <= 0) {
            $this->_importErrors[] = __(
                'Please correct ind_qty  %1 in row #%2.',
                $row['ind_qty'],
                $rowNumber
            );
        }

        /* validate ind_qty */
        $valueTo = (int)$row['global_qty'];
        if ($valueTo <= 0) {
            $this->_importErrors[] = __(
                'Please correct global_qty  %1 in row #%2.',
                $row['global_qty'],
                $rowNumber
            );
        }

         /* validate ind_qty */
        $type = $row['type'];
        if ($type != 'Fixed' && $type != 'Percentage') {
            $this->_importErrors[] = __(
                'Please correct type  %1 in row #%2.',
                $row['type'],
                $rowNumber
            );
        }

        /* validate discount */
        $valueTo = $this->_parseDecimalValue((int)$row['discount']);
        if ($valueTo === false) {
            $this->_importErrors[] = __(
                'Please correct discount  %1 in row #%2.',
                $row['discount'],
                $rowNumber
            );
        }

        /* validate discount */
        $valueTo = $row['customer_group'];
        if ($valueTo === '') {
            $this->_importErrors[] = __(
                'Please correct customer_group  %1 in row #%2.',
                $row['customer_group'],
                $rowNumber
            );
        }

        return $this->_importErrors;
    }

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

    protected function importFromCsvFile($file)
    {
        if (!isset($file)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $importProductRawData = $this->csvProcessor->getData($file['tmp_name']);
        $headers = $importProductRawData[0];
        unset($importProductRawData[0]);
        $predefinedHeaders = array_keys($this->feilds);

        if ($headers === false) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct offers file format.'));
        }
        if (array_diff($predefinedHeaders, $headers)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Sample file and Uploading file\'s header fields are not matched.'));
        }
        $fields = $predefinedHeaders;
        $updatedDataCount = 0;
        $newDataCount = 0;
        $productIds = $parentIds = $products = [];

        foreach ($importProductRawData as $rowIndex => $dataRow) {
            $rowData = [];
            foreach ($fields as $key => $field) {
                if (in_array($field, ['status'])) {
                    $rowData[$field] = (isset($dataRow[$key])) ? $dataRow[$key] : null;
                } else {
                    $rowData[$field] = (!empty($dataRow[$key])) ? trim($dataRow[$key]) : null;
                }
            }
            if (!empty($rowData)) {
                try {

                    // find vip model
                    $model = $this->vipProductsFactory->create()->getCollection()
                            ->addFieldToFilter('product_id', $rowData['product_id'])
                            ->addFieldToFilter('vendor_id', $rowData['vendor_id'])
                            ->getFirstItem();
                    if(!$model->getID()){
                        $newDataCount++;
                        $model = $this->vipProductsFactory->create();
                        $rowData['discount_type'] = $rowData['type'];
                        $model->setData($rowData);
                    }
                    else{
                        $model = $this->vipProductsFactory->create()->load($model->getID());
                        $updatedDataCount++;
                        $model->setIndQty($rowData['ind_qty']);
                        $model->setGlobalQty($rowData['global_qty']);
                        $model->setDiscountType($rowData['type']);
                        $model->setDiscount($rowData['discount']);
                        $model->setCustomerGroup($rowData['customer_group']);
                    }
                    $model->save();
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
                
            }
        }
        return ['newDataCount' => $newDataCount,'updatedDataCount' => $updatedDataCount];
    }

}
