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
namespace Magedelight\Catalog\Model;

use Magedelight\Catalog\Api\ProductManagementInterface;
use Magedelight\Catalog\Model\Product;
use Magedelight\Catalog\Api\VendorProductRepositoryInterface;
use Magedelight\Catalog\Model\ProductRequest;
use \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory as ProductWebsiteCollectionFactory;

/**
 * Handle various vendor product actions
 * @author Rocket Bazaar Core Team
 * Created at 04 April, 2016 2:20:31 PM
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProductManagement implements ProductManagementInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var VendorProductRepositoryInterface
     */
    protected $vendorProductRepository;

    /**
     * @var ProductWebsiteCollectionFactory
     */
    protected $productWebsiteCollectionFactory;

    /**
     * ProductManagement constructor.
     * @param ProductRequestRepository $productRequestRepository
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magedelight\Sales\Api\Data\CustomMessageInterface $customMessageInterface
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param VendorProductRepositoryInterface $vendorProductRepository
     * @param ProductWebsiteCollectionFactory $productWebsiteCollectionFactory
     */
    public function __construct(
        ProductRequestRepository $productRequestRepository,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magedelight\Sales\Api\Data\CustomMessageInterface $customMessageInterface,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        VendorProductRepositoryInterface $vendorProductRepository,
        ProductWebsiteCollectionFactory $productWebsiteCollectionFactory
    ) {
        $this->productRequestRepository = $productRequestRepository;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->_cacheManager            = $cacheManager;
        $this->_eventManager            = $eventManager;
        $this->customMessageInterface   = $customMessageInterface;
        $this->request = $request;
        $this->userContext = $userContext;
        $this->vendorProductRepository = $vendorProductRepository;
        $this->productWebsiteCollectionFactory = $productWebsiteCollectionFactory;
    }

    /**
     * Reject a product request. Perform necessary business operations like sending email.
     *
     * @param type $requestId
     * @return ProductManagementInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Exception
     * @api
     */
    public function rejectProductRequest($requestId)
    {
        $productRequest = $this->productRequestRepository->getById($requestId);
        $productRequest->setStatus(ProductRequest::STATUS_DISAPPROVED)->save();

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }
        $storeId = 0;
        $store = $this->storeManager->getStore($storeId);
        $receiverEmail = $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFICATION_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (($receiverEmail != "") && ($receiverEmail != null)) {
            $this->sendEmailAdminTemplate(
                $this->getVendor(),
                $types[$type],
                self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY,
                $receiverEmail,
                ['vendor' => $this->getVendor(), 'product_request' => $productRequest, 'store' => $store],
                $storeId
            );
        }
        return $this;
    }

    /**
     * Add offer on existing product.
     *
     * @param $vendor
     * @param $template
     * @param $sender
     * @param $receiverEmail
     * @param array $templateParams
     * @param null $storeId
     * @return ProductManagementInterface
     * @api
     */
    protected function sendEmailAdminTemplate(
        $vendor,
        $template,
        $sender,
        $receiverEmail,
        $templateParams = [],
        $storeId = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => 0])
            ->setTemplateVars($templateParams)
            ->setFromByScope('general')
            ->addTo($receiverEmail)
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function listUnlistProduct($productId, $type)
    {
        try {
            $productIds = [];
            if (strpos($productId, ',') !== false) {
                $productIds[] =  explode(',', $productId);
            } else {
                $productIds[] = $productId;
            }

            $collection = $this->productWebsiteCollectionFactory->create();
            $collection->getSelect()->join(
                ['mdvp' => 'md_vendor_product'],
                'mdvp.vendor_product_id = main_table.vendor_product_id',
                ['marketplace_product_id','qty']
            );
            $collection->addFieldToFilter('main_table.vendor_product_id', ['in'=> [$productIds]]);

            switch ($type) {
                case 'list':
                    $collection->addFieldToFilter('main_table.status', Product::STATUS_UNLISTED);
                    $message = '%1 products listed.';
                    $status = Product::STATUS_LISTED;
                    $event = 'frontend_vendor_product_mass_list_after';
                    break;
                case 'unlist':
                    $collection->addFieldToFilter('main_table.status', Product::STATUS_LISTED);
                    $message = '%1 products unlisted.';
                    $status = Product::STATUS_UNLISTED;
                    $event = 'frontend_vendor_product_mass_unlist_after';
                    break;
                default:
                    # code...
                    break;
            }

            $productIds = $products = [];
            $updatedRecordsCount = 0;
            if ($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $item->setStatus($status);
                    $item->save();
                    $productIds[] = $item->getMarketplaceProductId();
                    $products[$item->getVendorProductId()]['qty'] = $item->getQty();
                    $products[$item->getVendorProductId()]['marketplace_product_id'] = $item->getMarketplaceProductId();
                    $updatedRecordsCount++;
                }
            }
            if ($updatedRecordsCount > 0) {
                $eventParams = [
                    'marketplace_product_ids' => $productIds ,
                    'vendor_products' => $products
                ];

                $this->_eventManager->dispatch($event, $eventParams);
            }

            $this->customMessageInterface->setMessage(__($message, $updatedRecordsCount));
            $this->customMessageInterface->setStatus(true);
        } catch (\Exception $e) {
            $this->customMessageInterface->setMessage($e->getMessage());
            $this->customMessageInterface->setStatus(false);
        }

        return $this->customMessageInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function quickEdit($vendorProductId)
    {
        try {
            $vendorId = $this->userContext->getUserId();

            $model = $this->vendorProductRepository->getById($vendorProductId);
            $ProductWebsite = $this->productWebsiteRepository->getProductWebsiteData((int)$vendorProductId);

            $oldQty = $model->getQty();
            $model->setData('vendor_id', $vendorId);
            $model->setData('qty', $this->request->getParam('qty'));

            $special_from_date = $this->request->getParam('special_from_date');
            $special_to_date = $this->request->getParam('special_to_date');

            $specialPrice = ($this->request->getParam('special_price')) ?: null;
            $ProductWebsite->setData('special_price', $specialPrice);
            $ProductWebsite->setData('special_from_date', $special_from_date);
            $ProductWebsite->setData('special_to_date', $special_to_date);
            $ProductWebsite->setData('reorder_level', $this->request->getParam('reorder_level'));
            $ProductWebsite->setData('price', $this->request->getParam('price'));

            $this->vendorProductRepository->save($model);
            $this->productWebsiteRepository->save($ProductWebsite);

            $eventParams = [
                'marketplace_product_id' => $model->getMarketplaceProductId(),
                'old_qty' => $oldQty,
                'vendor_product' => $model
            ];
            $this->_eventManager->dispatch('frontend_vendor_product_save_after', $eventParams);
            $this->_cacheManager->clean('catalog_product_' . $model->getMarketplaceProductId());

            $this->customMessageInterface->setMessage(__('Product saved successfully'));
            $this->customMessageInterface->setStatus(true);
        } catch (\Exception $e) {
            $this->customMessageInterface->setMessage($e->getMessage());
            $this->customMessageInterface->setStatus(false);
        }
        return $this->customMessageInterface;
    }
}
