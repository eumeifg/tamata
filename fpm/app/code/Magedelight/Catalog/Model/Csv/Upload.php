<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\Csv;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Upload implements \Magedelight\Catalog\Api\CatalogCsvInterface
{
    const XML_PATH_EXCLUDE_ATTRIBUTES = 'vendor_product/vital_info/attributes';
    const XML_PATH_DEFAULT_ATTRIBUTE_SET = 'vendor_product/vital_info/attributeset';

    protected $currentCsvSkus = [];

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected $_category;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magedelight\Catalog\Helper\Bulkimport\Data
     */
    protected $_bulkimportHelper;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Vendor\Model\VendorRegistry
     */
    protected $vendorRegistry;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequest;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestManagement
     */
    protected $productRequestManagement;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Simple\Add
     */
    protected $addSimpleProductRequest;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Add
     */
    protected $addConfigurableProductRequest;

    /**
     * @var \Magedelight\Backend\App\Action\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $_eavAttribute
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magedelight\Catalog\Helper\Bulkimport\Data $_bulkimportHelper
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Catalog\Model\ProductRequestManagement $productRequestManagement
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Simple\Add $addSimpleProductRequest
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Add $addConfigurableProductRequest
     * @param \Magento\Framework\Escaper $_escaper
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $_eavAttribute,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Eav\Model\Config $eavConfig,
        \Magedelight\Catalog\Helper\Bulkimport\Data $_bulkimportHelper,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magedelight\Catalog\Model\ProductRequestManagement $productRequestManagement,
        \Magedelight\Catalog\Model\Product\Request\Type\Simple\Add $addSimpleProductRequest,
        \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Add $addConfigurableProductRequest,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->context = $context;
        $this->jsonHelper = $jsonHelper;
        $this->vendorRegistry = $vendorRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
        $this->csvProcessor = $csvProcessor;
        $this->transportBuilder = $transportBuilder;
        $this->productRequest = $productRequestFactory->create();
        $this->scopeConfig = $scopeConfig;
        $this->_bulkimportHelper = $_bulkimportHelper;
        $this->_eavAttribute = $_eavAttribute;
        $this->productRequestManagement = $productRequestManagement;
        $this->_escaper = $_escaper;
        $this->helper = $helper;
        $this->addConfigurableProductRequest = $addConfigurableProductRequest;
        $this->addSimpleProductRequest = $addSimpleProductRequest;
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function upload($sellerId, $cid)
    {
        $_filesParam = $this->context->getRequest()->getFiles()->toArray();
        $this->vendorRegistry->retrieve($sellerId);
        $result = [];
        if (count($_filesParam) < 1 || !isset($_filesParam['catalog-upload'])) {
            $result['html'] = 'Please Upload CSV file.';
            return $this->jsonHelper->jsonEncode($result);
        }
        $ext = $this->file->getPathInfo($_filesParam['catalog-upload']['name'])['extension'];
        if ($ext == 'csv') {
            try {
                $catalogdata = $this->csvProcessor->getData($_filesParam['catalog-upload']['tmp_name']);
            } catch (\Exception $e) {
                $result['html'] = __("Something went wrong. Please try again.");
                return $this->jsonHelper->jsonEncode($result);
            }

            if (!empty($catalogdata)) {
                $data = $this->getCatalogData($catalogdata, $sellerId, $cid);

                $totalRows = count($data);

                if (!empty($data['errors']) && count($data['errors']) > 0) {
                    $result['html'] = implode(' | ', $data['errors']);
                } else {
                    try {
                        if ((!empty($data['configurable']) && count($data['configurable']) > 0)) {
                            foreach ($data['configurable'] as $key => $value) {
                                $csvData = $this->prepareCsvData($data['configurable'][$key], true);
                                $this->addConfigurableProductRequest->execute($sellerId, $csvData, true);
                            }
                        }
                        if ((!empty($data['simple']) && count($data['simple']) > 0)) {
                            foreach ($data['simple'] as $key => $value) {
                                $csvData = $this->prepareCsvData($data['simple'][$key]);
                                $this->addSimpleProductRequest->execute(
                                    $sellerId,
                                    $csvData,
                                    true,
                                    true
                                );
                            }
                        }
                        $result['html'] = __("Your catalog has been successfully imported.");
                        $this->sendAdminNotification();
                    } catch (\Exception $e) {
                        $result['html'] = __($e->getMessage());
                    }
                }
            } else {
                $result['html'] = __("Your csv file contains invalid data.");
            }
        } else {
            $result['html'] = __("Invalid file format. Please upload file of type csv.");
        }
        return $this->jsonHelper->jsonEncode($result);
    }

    protected function getCatalogData($catalogData = [], $vendorId, $categoryId)
    {
        if (!empty($catalogData) && $categoryId != 'Select Category' && $categoryId != '') {
            /* Get attributes of category */
            $storeId = $this->storeManager->getStore()->getId();
            $this->_category = $category = $this->categoryRepository->get($categoryId, $storeId);
            $attribute_set_id = $this->_getCategoryAttriButeSet();

            $attributes = $this->getCategoryAttributes($attribute_set_id);

            $vitalFields = $this->_bulkimportHelper->getVitalFields();
            foreach ($attributes as $key => $value) {
                $attribute = $this->eavConfig->getAttribute('catalog_product', $value);
                if ($attribute->getIsRequired()) {
                    $vitalFields[] = $value;
                }
            }

            $data = [];
            $formattedData = [];
            $configurableSkus = [];
            $vendor_specific_variant_attributes = [];
            $error = '';
            $errors = [];
            $count = 0;
            $firstRow = true;
            $headers = array_keys($this->_bulkimportHelper->getExtraAttributeHeaders(
                $this->_bulkimportHelper->getCSVHeaders(),
                $attributes
            ));
            try {
				$validateImage = [];
                foreach ($catalogData as $key => $data) {
                    $rowData = [];
                    $variant_product_attributes = [];
                    if ($firstRow) {
                        $firstRow = false;
                    } else {
                        $count++;
                        if (!empty($data)) {
                            $emptyVitalAttributes = [];
                            $invalidFields = [];
                            foreach ($catalogData[0] as $key => $field) {
                                if (!in_array($field, $headers)) {
                                    $invalidFields[] = trim($field);
                                } else {
                                    $tempName = "special_price";
                                    if ($field === $tempName) {
                                        $price = $data[$key-1];
                                        $special_price = $data[$key];
                                        if ($special_price > $price) {
                                            $error = __("Special Price shouldn't be greater than Price.");
                                            $errors['special_price_invalid'] = $error;
                                            break;
                                        }
                                    }
                                    $rowData[trim($field)] = trim($data[$key]);
                                }
                            }

                            if (isset($errors['special_price_invalid'])) {
                                break;
                            }
                            if (isset($rowData['category_id']) && $categoryId != $rowData['category_id']) {
                                if (empty($errors['category_mismiatch'])) {
                                    $error = __('Selected category and category in csv file does not match.');
                                    $errors['category_mismiatch'] = $error;
                                    break;
                                }
                            } elseif (!isset($rowData['category_id'])) {
                                if (empty($errors['invalid_csv'])) {
                                    $error = __('Please check your csv file and make sure fields match
                                     with the downloaded sample file.');
                                    $errors['invalid_csv'] = $error;
                                    break;
                                }
                            }

                            if (!empty($invalidFields) && empty($errors['invalid_field'])) {
                                $errors['invalid_field'] = __('Please remove following invalid fields from the csv
                                 file [%1].', implode(', ', $invalidFields));
                                break;
                            }
                            if (isset($rowData['has_variants']) &&
                                $rowData['has_variants'] == '1' &&
                                !empty($rowData['super_sku'])) {
                                if (empty($errors['invalid_configuration'])) {
                                    $errors['invalid_configuration'] = __('Configurable product(s) can not be a variant
                                     , Please check your configuration.');
                                    break;
                                }
                            }
                            $noProductAttributeExist = [];
                            foreach ($vitalFields as $key => $value) {
                                if (!array_key_exists($value, $rowData)) {
                                    $noProductAttributeExist[] = $value;
                                }
                            }
                            /*********** Validate same image exists starts***********/
							foreach ($rowData as $key => $value) {
								if ($key == 'images') {
									$rowImages = [];
									$rowImages = explode(",", $rowData['images']);
									if(count(array_intersect($rowImages, $validateImage)) != 0) {
										$errors['invalid_images'] = __('Please upload unique images. one or more rows contains duplicate images on row #%1', $count);
										break;
									}
									$validateImage = array_merge($validateImage, $rowImages);
								}
							}
							/*********** Validate same image exists ends***********/
                            if (empty($noProductAttributeExist)) {
                                foreach ($rowData as $key => $value) {
                                    if ($rowData['has_variants'] == '1') {
                                        if (in_array(
                                            $key,
                                            $this->_bulkimportHelper->getVitalFields(Configurable::TYPE_CODE)
                                        ) && trim($rowData[$key]) == ''
                                        ) {
                                            $emptyVitalAttributes[] = $key;
                                        }
                                    } else {
                                        if (in_array($key, $vitalFields) && trim($rowData[$key]) == '') {
                                            $emptyVitalAttributes[] = $key;
                                        }
                                    }
                                }
                            }

                            if (!empty($rowData) && $categoryId && $attribute_set_id) {
                                if (!empty($noProductAttributeExist) && count($noProductAttributeExist) > 0) {
                                    if (empty($errors['no_product_attribute_exist'])) {
                                        $error = __('Please check your csv file and fields.');
                                        $errors['no_product_attribute_exist'] = $error;
                                        break;
                                    }
                                }

                                $vendorSku = $rowData['vendor_sku'];
                                $error = $this->productRequest->validateUniqueVendorSku($vendorId, $vendorSku);
                                if ($error) {
                                    if (empty($errors['invalid_sku'])) {
                                        $errors['invalid_sku'] = __('Vendor sku already exists in pending request');
                                        break;
                                    }
                                }
                                if (in_array($vendorSku, $this->currentCsvSkus)) {
                                    $errors['invalid_sku'] = __('Duplicate Vendor sku found on row #%1', $count);
                                    break;
                                }
                                $this->currentCsvSkus[] = $vendorSku;

                                /* Set attribute values for each product. */
                                $additional_attributes_array = [];
                                $used_product_attribute_ids = [];
                                if ($attributes) {
                                    foreach ($attributes as $key => $value) {
                                        $attribute = $this->eavConfig->getAttribute('catalog_product', $value);
                                        if ($attribute->getFrontendInput() == 'select') {
                                            if (isset($rowData[$value])) {
                                                $modifiedData = $attribute->getSource()->getOptionId($rowData[$value]);
                                                if ($attribute->getFrontendInput() == 'select' &&
                                                    $attribute->getIsUserDefined() &&
                                                    $attribute->getIsGlobal()) {
                                                    $used_product_attribute_ids[$key] = $value;
                                                    $variant_product_attributes[$value] = $modifiedData;
                                                }
                                                $additional_attributes_array[$value] = $this->_escaper->escapeHtml(
                                                    $modifiedData
                                                );
                                            }
                                        } else {
                                            if ($attribute->getFrontendInput() == 'multiselect') {
                                                $options = explode(',', $rowData[$value]);
                                                $options = array_filter($options);
                                                $multiSelectData = [];

                                                if (!empty($options)) {
                                                    foreach ($options as $option) {
                                                        $optionId = $attribute->getSource()->getOptionId($option);
                                                        if ($optionId) {
                                                            $multiSelectData[] = $attribute->getSource()
                                                                ->getOptionId($option);
                                                        }
                                                    }
                                                }
                                                $modifiedData = $multiSelectData;
                                                if ($attribute->getFrontendInput() == 'multiselect' &&
                                                    $attribute->getIsUserDefined() &&
                                                    $attribute->getIsGlobal()) {
                                                    $variant_product_attributes[$value] = $modifiedData;
                                                }
                                                $additional_attributes_array[$value] = $this->_escaper->escapeHtml(
                                                    $modifiedData
                                                );
                                            } else {
                                                $additional_attributes_array[$value] = trim(
                                                    $this->_escaper->escapeHtml($rowData[$value])
                                                );
                                            }
                                        }
                                    }
                                }
                                if (!empty($emptyVitalAttributes) && count($emptyVitalAttributes) > 0) {
                                    if (empty($errors['vital_data_missing'])) {
                                        $errors['vital_data_missing'] = __('Please check following mandatory fields in
                                         your csv file : [ %1 ]', implode(', ', $emptyVitalAttributes));
                                    }
                                }
                                /* Set attribute values for each product.
                                 * Validate for duplicate variants combination
                                 * according to main/parent configurable product. */
                                if (!empty($variant_product_attributes) && !empty($rowData['super_sku'])) {
                                    if (!empty($vendor_specific_variant_attributes[$rowData['super_sku']])) {
                                        if (in_array(
                                            $variant_product_attributes,
                                            $vendor_specific_variant_attributes[$rowData['super_sku']]
                                        )) {
                                            if (empty($errors['invalid_variant'])) {
                                                $errors['invalid_variant'] = __('Please check variant combinations for 
                                                the configurable products, seems duplicate/invalid data.');
                                            }
                                        }
                                    }
                                    $vendor_specific_variant_attributes[$rowData['super_sku']][] =
                                        $variant_product_attributes;
                                }
                                /* Validate for duplicate variants combination
                                according to main/parent configurable product. */

                                $images = $this->jsonHelper->jsonEncode(
                                    $this->getImageContent(strtolower($rowData['images']))
                                );
                                $data = [
                                    'vendor_id' => $vendorId,
                                    'main_category_id' => $categoryId,
                                    'category_id' => $categoryId,
                                    'attribute_set_id' => $attribute_set_id,
                                    'status' => '0',
                                    'name' => $rowData['name'],
                                    'has_variants' => $rowData['has_variants'],
                                    'vendor_sku' => $rowData['vendor_sku'],
                                    'condition' => $rowData['condition'],
                                    'condition_note' => $rowData['condition_note'],
                                    'price' => $rowData['price'],
                                    'special_price' => $rowData['special_price'],
                                    'special_from_date' => $rowData['special_from_date'],
                                    'special_to_date' => $rowData['special_to_date'],
                                    'qty' => $rowData['qty'],
                                    'reorder_level' => $rowData['reorder_level'],
                                    'used_product_attribute_ids' => $this->jsonHelper->jsonEncode(
                                        $used_product_attribute_ids
                                    ),
                                    'images' => $images,
                                    'warranty_type' => $rowData['warranty_type'],
                                    'warranty_description' => $rowData['warranty_description'],
                                    'store_id' => $storeId
                                ];
                                $base_image = $this->helper->getTmpFileIfExists(
                                    strtolower($rowData['base_image']),
                                    true
                                );
                                $data['base_image'] = $this->jsonHelper->jsonEncode($base_image);
                                $additional_attributes_array['image'] = $base_image;
                                $additional_attributes_array['thumbnail'] = $base_image;
                                $additional_attributes_array['small_image'] = $base_image;
                                $data['attributes'] = $this->jsonHelper->jsonEncode($additional_attributes_array);

                                // Check if product type is configurable
                                if ($rowData['has_variants'] == '1') {
                                    $configurableSkus[] = $rowData['vendor_sku'];
                                    $configurableParentData = $this->getConfigurableProductFields($data);
                                    $formattedData['configurable'][$rowData['vendor_sku']] = $configurableParentData;
                                } else {
                                    if ($rowData['has_variants'] == 0 && !empty($rowData['super_sku'])) {
                                        /* Check if product type is a variant/child of configurable product. */
                                        if (in_array($rowData['super_sku'], $configurableSkus)) {
                                            $configurableChildData = $this->getConfigurablesChildFields(
                                                $data,
                                                $variant_product_attributes
                                            );
                                            $formattedData['configurable'][$rowData['super_sku']]['variants_data'][] =
                                                $configurableChildData;
                                        } else {
                                            $errors['simple'] = __("Please check super_sku in your csv file.
                                             If product haven't variants than it must be blank");
                                        }
                                    } else {
                                        /* Check if product type is simple */
                                        $formattedData['simple'][] = $data;
                                    }
                                }
                            }
                        }
                    }
                    if (count($errors) > 0) {
                        break;
                    }
                }
                if ($count == 0) {
                    $errors['vital_data_missing'] = __('No data found to import, please check CSV.');
                }
            } catch (\Exception $e) {
                $errors['exception'] = __('exception, %1', $e->getMessage());
            }
            $formattedData['errors'] = (count($errors) > 0) ? $errors : null;
            return $formattedData;
        } else {
            $error = __('Please select appropriate category or data.');
            $errors['category_selection'] = $error;
            $formattedData['errors'] = (count($errors) > 0) ? $errors : null;
            return $formattedData;
        }
    }

    protected function getCategoryAttributes($attribute_set_id = null)
    {
        if ($attribute_set_id) {
            $attributes = $this->getAttributesByAttributeSet($attribute_set_id);
            $excludeAttributes = $this->getExcludeAttributeList();
            $systemExcludedAttributes = $this->_bulkimportHelper->getSystemExcludedAttributes();
            foreach ($systemExcludedAttributes as $code) {
                $excludeAttributes[] = $this->getAttriButeIdByCode($code);
            }
            /* Set category attributes. */
            if ($attributes) {
                foreach ($attributes as $key => $attribute) {
                    if (in_array($key, $excludeAttributes)) {
                        unset($attributes[$key]);
                    }
                }
            }
            return $attributes;
        }
    }

    protected function getImageContent($argImages = null)
    {
        $imageData = [];
        if (!empty($argImages)) {
            $images = explode(",", $argImages);
            $images = array_filter($images);
            $images = array_unique($images);
            $counter = 0;
            $filesToDelete = [];
            foreach ($images as $image) {
                $counter++;
                $image_path = $this->helper->getTmpFileIfExists($image, true);
                if ($image_path) {
                    $imageId = $this->generateRandomString();
                    $imageData[$imageId] = [
                        "position"   => $counter,
                        "media_type" => 'image',
                        "file"       => $image_path,
                        "value_id"   => '',
                        "label"      => '',
                        "disabled"   => '0',
                        "removed"    => '',
                        "thumbnail"  => $image_path,
                        "small_image" => $image_path
                    ];
                } else {
                    $counter--;
                }
            }
            return $imageData;
        }
    }

    public function generateRandomString($length = 18)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getConfigurableProductFields($rowData)
    {
        $data = [
            'vendor_id' => $rowData['vendor_id'],
            'main_category_id' => $rowData['category_id'],
            'category_id' => $rowData['category_id'],
            'attribute_set_id' => $rowData['attribute_set_id'],
            'status' => $rowData['status'],
            'name' => $rowData['name'],
            'has_variants' => $rowData['has_variants'],
            'vendor_sku' => $rowData['vendor_sku'],
            'used_product_attribute_ids' => $rowData['used_product_attribute_ids'],
            'images' => $rowData['images'],
            'base_image' => $rowData['base_image'],
            'attributes' => $rowData['attributes'],
            'store_id' => $rowData['store_id'],
        ];
        return $data;
    }

    public function getConfigurablesChildFields($rowData, $variant_product_attributes)
    {
        $newData = [];
        $file = $this->jsonHelper->jsonDecode($rowData['images']);
        $finalImage = '';
        if (is_array($file)) {
            foreach ($file as $image) {
                $finalImage = $image['file'];
            }
        }
        $data = [
            'vendor_sku' => $rowData['vendor_sku'],
            'condition' => $rowData['condition'],
            'condition_note' => $rowData['condition_note'],
            'price' => $rowData['price'],
            'special_price' => $rowData['special_price'],
            'special_from_date' => $rowData['special_from_date'],
            'special_to_date' => $rowData['special_to_date'],
            'qty' => $rowData['qty'],
            'reorder_level' => $rowData['reorder_level'],
            'warranty_type' => $rowData['warranty_type'],
            'warranty_description' => $rowData['warranty_description'],
            'image' => ['images' => $rowData['images'], 'image' => $finalImage],
            'store_id' => $rowData['store_id']
        ];
        $newData = array_merge($data, $variant_product_attributes);
        return $newData;
    }

    public function sendAdminNotification()
    {
        $bulkimportNotificationEnable = $this->_bulkimportHelper->isNotificationEnabled();
        $getAdminNotificationEmail = $this->_bulkimportHelper->getAdminEmail();
        if ($bulkimportNotificationEnable && !empty($getAdminNotificationEmail)) {
            $templateVars = [
                'product_message' => __("Bulk Import Product Request")
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('vendor_product_bulklist_admin_template')
                ->setTemplateOptions(
                    [
                        'area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($getAdminNotificationEmail)
                ->getTransport();
            try {
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $e) {
                throw new \Magento\Framework\Exception\MailException(
                    __('Email could not be sent. Please try again or contact us.')
                );
            }
        }
    }

    protected function _getCategoryAttriButeSet()
    {
        return $this->_attributeSetId = $this->_getAttributeSetIdRecursive(
            $this->_category->getMdCategoryAttributeSetId(),
            $this->_category->getId()
        );
    }

    /**
     *
     * @param int $setId
     * @param int $categoryId
     * @return int $setId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getAttributeSetIdRecursive($setId, $categoryId)
    {
        if (($setId === null)) {
            return $this->_getCategoryDefaultAttributeSetId();
        }
        if (intval($setId) > 0) {
            return $setId;
        } else {
            $this->_category = $this->categoryRepository->get($categoryId);
            $setId = $this->categoryRepository->get($this->_category->getParentId())->getMdCategoryAttributeSetId();
            return $this->_getAttributeSetIdRecursive($setId, $this->_category->getParentId());
        }
    }

    /**
     *
     * @return Category Default Attribute setId
     */
    protected function _getCategoryDefaultAttributeSetId()
    {
        return $this->_bulkimportHelper->getConfigValue(self::XML_PATH_DEFAULT_ATTRIBUTE_SET);
    }

    protected function getAttributesByAttributeSet($attribute_set_id = null)
    {
        $categoryAttributes = [];
        /** @var  $coll \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection */
        $eavAttributeCollection = $this->eavConfig->getEntityType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getAttributeCollection();

        $excludeAttributeList = $this->getExcludeAttributeList();
        $eavAttributeCollection->addFieldToFilter('main_table.attribute_id', ['nin' => $excludeAttributeList]);

        if ($attribute_set_id) {
            $eavAttributeCollection->setAttributeSetFilter($attribute_set_id);
        } else {
            $eavAttributeCollection->setAttributeSetFilter(
                $this->coreRegistry->registry('vendor_current_category')->getMdCategoryAttributeSetId()
            );
        }

        $eavAttributeCollection->load();
        foreach ($eavAttributeCollection as $eavAttributes) {
            $categoryAttributes[$eavAttributes->getAttributeId()] = $eavAttributes->getAttributeCode();
        }

        return $categoryAttributes;
    }

    public function getExcludeAttributeList()
    {
        $collection = $this->_bulkimportHelper->getConfigValue(self::XML_PATH_EXCLUDE_ATTRIBUTES);
        return explode(',', $collection);
    }

    protected function getAttriButeIdByCode($attributeCode)
    {
        return $attributeId = $this->_eavAttribute->getIdByCode('catalog_product', $attributeCode);
    }

    public function prepareCsvData($postData, $isConfig = false)
    {
        $offerData = [
            'attribute_set_id','main_category_id','marketplace_product_id',
            'category_id','product_request_id','vendor_sku','condition',
            'condition_note','price','special_price','special_from_date',
            'special_to_date','qty','reorder_level','warranty_type','warranty_description'
        ];

        $productData = [
            'images','image','small_image','thumbnail','swatch_image'
        ];

        $newData = [];
        foreach ($postData as $key => $dataValue) {
            if (in_array($key, $offerData)) {
                $newData['offer'][$key] = $dataValue;
            }
            $newData['offer']['product_request_id'] = '';
            $newData['offer']['marketplace_product_id'] = '';

            if ($key == 'store_id') {
                $newData['store'] = $dataValue;
            }
            if ($key == 'name') {
                $newData['vital'][$key] = $dataValue;
            }
            $newData['variantsAttributeCodes'] = '';
            $newData['variantsAttributeLabels'] = '';
            $newData['usedAttributeIds'] = $postData['used_product_attribute_ids'];
            $variantsAttributes = $this->jsonHelper->jsonDecode($postData['used_product_attribute_ids']);

            if (in_array($key, $productData)) {
                $newData['product'][$key] = $dataValue;
                if ($key == 'images') {
                    $newData['product'][$key] = $this->jsonHelper->jsonDecode($dataValue);
                }
            }

            if ($isConfig) {
                $newData['has_variants'] = 1;
                $newData['store'] = 'default';
            }

            if ($key == 'variants_data') {
                $newData['variants_data'] = $dataValue;
            }

            if ($key == 'attributes') {
                $newData['additional'] = [];
                $postDataAttributes = $this->jsonHelper->jsonDecode($dataValue);
                if (isset($postDataAttributes['image']) ||
                    isset($postDataAttributes['small_image']) ||
                    isset($postDataAttributes['thumbnail'])
                ) {
                    $newData['product']['image'] = $postDataAttributes['image'];
                    $newData['product']['small_image'] = $postDataAttributes['small_image'];
                    $newData['product']['thumbnail'] = $postDataAttributes['thumbnail'];
                }

                if (is_array($variantsAttributes) && !empty($variantsAttributes)) {
                    foreach ($variantsAttributes as $key => $code) {
                        $newData['variant'][$code] = $postDataAttributes[$code];
                    }
                }

                $postAttributes = $postDataAttributes;
                foreach ($postAttributes as $attKey => $valueAttributes) {
                    $newData['additional'][$attKey] = $valueAttributes;
                }
            }

            $newData['website_ids'] = [];
        }
        return $newData;
    }
}
