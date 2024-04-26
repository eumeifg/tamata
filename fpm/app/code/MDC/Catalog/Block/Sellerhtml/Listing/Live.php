<?php

namespace MDC\Catalog\Block\Sellerhtml\Listing;

class Live extends \Magedelight\Catalog\Block\Sellerhtml\Listing\Live
{
    /**
     * Live constructor.
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magedelight\Catalog\Model\VendorProduct\Listing\LiveProducts $liveProducts
     * @param \Magedelight\Catalog\Model\VendorProduct\Listing\ApprovedProducts $approvedProducts
     * @param array $data
     */
    public function __construct(\Magedelight\Backend\Block\Template\Context $context, \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory, \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory, \Magedelight\Backend\Model\Auth\Session $authSession, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Framework\Serialize\SerializerInterface $serializer, \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository, \Magedelight\Catalog\Helper\Data $catalogHelper, \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder, \Magedelight\Catalog\Model\VendorProduct\Listing\LiveProducts $liveProducts, \Magedelight\Catalog\Model\VendorProduct\Listing\ApprovedProducts $approvedProducts, array $data = [])
    {
        parent::__construct($context, $productRequestFactory, $vendorProductFactory, $authSession, $productFactory, $serializer, $attributeRepository, $catalogHelper, $imageBuilder, $liveProducts, $approvedProducts, $data);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLiveProductsCollection()
    {
        $collection = $this->liveProducts->getList(
            $this->getVendor()->getVendorId(),
            $this->getCurrentStoreId()
        );

        $searchQuery = $this->getRequest()->getParam('q');
        $data = $this->authSession->getGridSession();

        if ($searchQuery && $this->getRequest()->getParam('sfrm') == 'l') {
            // $str = preg_replace('/[^A-Za-z0-9()\-\_\ ]/', '', $searchQuery);
            $str = trim($searchQuery); //'/[^\w\s]+/u'

            $collection->getSelect()->where(
                'cpev.value like "%' . $str . '%" OR cpev_default.value like "%' .
                $str . '%" OR vendor_sku like "%' . $str . '%" OR cpev_barcode.value like "%' . $str . '%"'
            );
        }
        //echo $collection->getSelect(); die();
        if ($this->getRequest()->getParam('session-clear-product-live') == "1") {
            if ($data) {
                $product_live = $data['grid_session']['product_live'];
                foreach ($product_live as $key => $val) {
                    $product_live[$key] = '';
                }

                $gridsession = $this->authSession->getGridSession();
                $gridsession['grid_session']['product_live'] = $product_live;
                $this->authSession->setGridSession($gridsession);
            }
        }

        if ($this->getRequest()->getParam('ostock', 0)) {
            $collection->getSelect()->where('mdvp.qty <= 0');
        }

        $sortOrder = $this->getRequest()->getParam('sort_order', 'mdvp.vendor_product_id');
        if ($this->getRequest()->getParam('sfrm') != 'l') {
            $sortOrder = 'mdvp.vendor_product_id';
        }

        $collection->getSelect()->order($sortOrder . ' ' . $this->getRequest()->getParam('dir', "DESC"));
        return $collection;
    }
}
