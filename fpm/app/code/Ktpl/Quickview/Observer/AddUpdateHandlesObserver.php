<?php
namespace Ktpl\Quickview\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AddUpdateHandlesObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    const XML_PATH_QUICKVIEW_REMOVE_PRODUCT_IMAGE = 'ktpl_quickview/general/display_product_image';
    const XML_PATH_QUICKVIEW_REMOVE_PRODUCT_IMAGE_THUMB = 'ktpl_quickview/general/display_product_image_thumbnail';
    const XML_PATH_QUICKVIEW_REMOVE_AVAILABILITY = 'ktpl_quickview/general/display_availability';
    const XML_PATH_QUICKVIEW_REMOVE_ADDTOCART = 'ktpl_quickview/general/display_addtocart_button';
    const XML_PATH_QUICKVIEW_REMOVE_REVIEWS = 'ktpl_quickview/general/display_reviews';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Add New Layout handle
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getData('layout');
        $fullActionName = $observer->getData('full_action_name');

        if ($fullActionName != 'ktpl_quickview_catalog_product_view') {
            return $this;
        }

        $productId= $this->request->getParam('id');
        if (isset($productId)) {
            try {
                $product = $this->productRepository->getById(
                    $productId,
                    false,
                    $this->storeManager->getStore()->getId()
                );
            } catch (NoSuchEntityException $e) {
                return false;
            }

            $productType = $product->getTypeId();

            $layout->getUpdate()->addHandle('ktpl_quickview_catalog_product_view_type_' . $productType);
        }

        $displayProductImage = $this->scopeConfig->getValue(
            self::XML_PATH_QUICKVIEW_REMOVE_PRODUCT_IMAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$displayProductImage) {
            $layout->getUpdate()->addHandle('ktpl_quickview_removeproduct_image');
        }

        $displayProductImageThumb = $this->scopeConfig->getValue(
            self::XML_PATH_QUICKVIEW_REMOVE_PRODUCT_IMAGE_THUMB,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$displayProductImageThumb) {
            $layout->getUpdate()->addHandle('ktpl_quickview_removeproduct_image_thumb');
        }

        $displayAvailability = $this->scopeConfig->getValue(
            self::XML_PATH_QUICKVIEW_REMOVE_AVAILABILITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$displayAvailability) {
            $layout->getUpdate()->addHandle('ktpl_quickview_removeavailability');
        }

        $displayReviews = $this->scopeConfig->getValue(
            self::XML_PATH_QUICKVIEW_REMOVE_REVIEWS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$displayReviews) {
            $layout->getUpdate()->addHandle('ktpl_quickview_removereviews');
        }

        $displayAddtoCart = $this->scopeConfig->getValue(
            self::XML_PATH_QUICKVIEW_REMOVE_ADDTOCART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$displayAddtoCart) {
            $layout->getUpdate()->addHandle('ktpl_quickview_removeaddtocart');
        }

        return $this;
    }
}
