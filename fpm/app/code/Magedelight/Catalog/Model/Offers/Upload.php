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
namespace Magedelight\Catalog\Model\Offers;

class Upload implements \Magedelight\Catalog\Api\CatalogOffersInterface
{
    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_bulklist/template';
    /**
     * @var \Magedelight\Catalog\Helper\Bulkimport\Data
     */
    protected $dataHelper;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magedelight\Catalog\Helper\Bulkimport\Offers
     */
    protected $offersHelper;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequest;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Vendor\Model\VendorRegistry
     */
    protected $vendorRegistry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magedelight\Catalog\Api\VendorProductRepositoryInterface
     */
    protected $vendorProductRepository;

    /**
     * @var \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface
     */
    protected $productWebsiteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magedelight\Catalog\Helper\Bulkimport\Data $dataHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Catalog\Helper\Bulkimport\Offers $offersHelper
     * @param \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magedelight\Catalog\Api\VendorProductRepositoryInterface $vendorProductRepository
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magedelight\Catalog\Helper\Bulkimport\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magedelight\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Helper\Bulkimport\Offers $offersHelper,
        \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magedelight\Catalog\Api\VendorProductRepositoryInterface $vendorProductRepository,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->request = $context->getRequest();
        $this->csvProcessor = $csvProcessor;
        $this->_transportBuilder = $transportBuilder;
        $this->_bulkimportHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
        $this->productRequest = $productRequestFactory->create();
        $this->productFactory = $productFactory;
        $this->offersHelper = $offersHelper;
        $this->_storeManager = $storeManager;
        $this->vendorRegistry = $vendorRegistry;
        $this->_cacheManager = $cacheManager;
        $this->_eventManager = $eventManager;
        $this->jsonHelper = $jsonHelper;
        $this->vendorProductRepository = $vendorProductRepository;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->file = $file;
    }

    /**
     * @param integer $sellerId
     * @return string
     * @throws \Exception
     */
    public function upload($sellerId)
    {
        /* This essential for check vendor with given id exist or not */
        $result = [];
        $_filesParam = $this->request->getFiles()->toArray();

        if (count($_filesParam) < 1 || !isset($_filesParam['catalog-bulk-upload'])) {
            $result['error'] = 'Please upload a csv file.';
            return $this->jsonHelper->jsonEncode($result);
        }
        $this->vendorRegistry->retrieve($sellerId);
        $ext = $this->file->getPathInfo($_filesParam['catalog-bulk-upload']['name'])['extension'];
        $error = [];

        if ($ext == 'csv') {
            $catalogOfferData = $this->csvProcessor->getData($_filesParam['catalog-bulk-upload']['tmp_name']);
            $data = $this->getCatalogOfferData($catalogOfferData);
            if (!empty($data['errors']) && count($data['errors']) > 0) {
                $result['error'] = implode(' | ', $data['errors']);
            } else {
                try {
					$productIds = [];
                    $products = [];
                    $parentIds = [];
                    if ((!empty($data['offers']) && count($data['offers']) > 0)) {
                        foreach ($data['offers'] as $key => $value) {
                            /* Load vendor product using vendor_sku and vendor_id. */
                            $vendorProduct = $this->getVendorProduct($value['vendor_sku'], $sellerId);
                            $productIds[] = $vendorProduct->getMarketplaceProductId();
                            $id = $vendorProduct->getMarketplaceProductId();
							$products[$id]['qty'] = $vendorProduct->getQty();
							$products[$id]['marketplace_product_id'] = $vendorProduct->getMarketplaceProductId();
							if ($vendorProduct->getParentId()) {
								/* Empty Check is required. Throws validation issue in indexing. */
								$parentIds[] = $vendorProduct->getParentId();
							}
                            if ($vendorProduct) {
                                $vendorProductId = $vendorProduct->getVendorProductId();
                                $vendorProductQty = $this->vendorProductRepository->getById($vendorProductId);
                                $productWebsite = $this->productWebsiteRepository
                                    ->getProductWebsiteData((int) $vendorProductId);
                            } else {
                                $error['vendor_sku'][$key] = $value['vendor_sku'];
                            }
                            if ($vendorProduct) {
                                $this->productRequest->setData($vendorProduct->getData());
                                $websitesId = $this->_websiteId = $this->_storeManager->getWebsite()->getId();
                                $vendorProductQty->setData('qty', $value['qty']);
                                $productWebsite->setData('price', $value['price']);
                                $productWebsite->setData('special_price', $value['special_price']);
                                $productWebsite->setData('special_from_date', $value['special_from_date']);
                                $productWebsite->setData('special_to_date', $value['special_to_date']);
                                $productWebsite->setData('reorder_level', $value['reorder_level']);
                                $productWebsite->setData('website_id', $websitesId);
                                try {
                                    $this->productWebsiteRepository->save($productWebsite);
                                    $this->vendorProductRepository->save($vendorProductQty);
                                    $this->_cacheManager->clean('catalog_product_' . $vendorProduct->getMarketplaceProductId());
                                } catch (\Exception $e) {
                                    throw new \Exception($e->getMessage());
                                }
                            }
                        }

                        $parentIds = array_values($parentIds);
                        $eventParams = [
							'marketplace_product_ids' => $productIds,
							'vendor_products' => $products,
							'parent_ids' => array_unique($parentIds)
						];
						$this->_eventManager->dispatch('frontend_vendor_product_offer_upload_after', $eventParams);
                    }
                    if ($error) {
                        $result['error'] = __("Unable to find given product sku:" . ' '
                            . implode(',', $error['vendor_sku']));
                    } else {
                        $result['success'] = __("Your offer(s) has been successfully applied.");
                        $this->sendAdminNotification();
                    }
                } catch (\Exception $e) {
                    $result['error'] = __('Failed to apply your offer(s). Please try again. %1', $e->getMessage());
                }
            }
        } else {
            $result['error'] = "Invalid file format. Upload only csv file.";
        }
        return $this->jsonHelper->jsonEncode($result);
    }

    /**
     *
     * @param array $catalogOfferData
     * @return array
     */
    protected function getCatalogOfferData($catalogOfferData = [])
    {
        if (!empty($catalogOfferData)) {
            $vitalFields = $this->offersHelper->getVitalFields();
            $data = [];
            $formattedData = [];
            $errors = [];
            $firstRow = true;
            try {
                foreach ($catalogOfferData as $key => $data) {
                    $rowData = [];
                    if ($firstRow) {
                        $firstRow = false;
                    } else {
                        if (!empty($data)) {
                            $emptyVitalAttributes = [];
                            $noProductAttributeExist = [];
                            foreach ($catalogOfferData[0] as $key => $field) {
                                $rowData[trim($field)] = (!empty($data[$key])) ? trim($data[$key]) : null;
                            }
                            foreach ($rowData as $key => $value) {
                                if (in_array($key, $vitalFields) && trim($rowData[$key]) == '') {
                                    $emptyVitalAttributes[] = $key;
                                } else {
                                    $excluded = [
                                        'special_price',
                                        'special_from_date',
                                        'special_to_date',
                                        'reorder_level'
                                    ];
                                    if (in_array($key, $excluded)) {
                                        continue;
                                    }
                                    if (!in_array($key, $vitalFields)) {
                                        $noProductAttributeExist[] = $key;
                                    }
                                }
                            }

                            if (!empty($rowData)) {
                                if (!empty($emptyVitalAttributes) && count($emptyVitalAttributes) > 0) {
                                    if (empty($errors['vital_data_missing'])) {
                                        $errors['vital_data_missing'] = __(
                                            'Please check following mandatory fields in your csv file : [ %1 ]',
                                            implode(', ', $emptyVitalAttributes)
                                        );
                                    }
                                }
                                if (!empty($noProductAttributeExist) && count($noProductAttributeExist) > 0) {
                                    if (empty($errors['no_product_attribute_exist'])) {
                                        $errors['no_product_attribute_exist'] = __(
                                            'Please check your csv file and fields : [ %1 ]',
                                            implode(', ', $noProductAttributeExist)
                                        );
                                    }
                                }
                                if (empty($errors)) {
                                    $data = [
                                        'vendor_sku'        => $rowData['vendor_sku'],
                                        'price'             => $rowData['price'],
                                        'special_price'     => $rowData['special_price'],
                                        'special_from_date' => $rowData['special_from_date'],
                                        'special_to_date'   => $rowData['special_to_date'],
                                        'qty'               => $rowData['qty'],
                                        'reorder_level'     => $rowData['reorder_level'],
                                    ];
                                    $formattedData['offers'][] = $data;
                                }
                            }
                        }
                    }
                    if (count($errors) > 0) {
                        break;
                    }
                }
                if (empty($formattedData['offers']) && empty($errors['no_product_attribute_exist'])) {
                    $errors['blank_row_csv'] = __('Please insert appropriate data to update offer details.');
                }
            } catch (\Exception $e) {
                $errors['exception'] = __('exception, %1', $e->getMessage());
            }
            $formattedData['errors'] = (count($errors) > 0) ? $errors : null;
            return $formattedData;
        }
    }

    /**
     * @param string $vendorSku
     * @param integer $sellerId
     * @return type
     */
    protected function getVendorProduct($vendorSku = '', $sellerId)
    {
        if ($vendorSku) {
            $model = $this->productFactory->create()->getCollection()
                ->addFieldToFilter('vendor_sku', $vendorSku)
                ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
                ->addFieldToFilter('vendor_id', $sellerId)->getFirstItem();

            if ($model->getId()) {
                return $model;
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function sendAdminNotification()
    {
        $bulkimportNotificationEnable = $this->_bulkimportHelper->isNotificationEnabled();
        $getAdminNotificationEmail = $this->_bulkimportHelper->getAdminEmail();

        if ($bulkimportNotificationEnable && !empty($getAdminNotificationEmail)) {
            $templateVars = [
                'product_message' => "Bulkoffer Update Product Request"
            ];
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                ->setTemplateOptions(
                    [
                        'area'  => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
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
}
