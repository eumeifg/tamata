<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_PromoCallouts
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace Ktpl\PromoCallouts\Block\HomePage;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Sales\Model\Order as VendorOrder;

class BestSellers extends \Magento\Framework\View\Element\Template
{

    const LIMIT = 15;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $micrositeHelper;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Image
     */
    protected $imageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper
     * @param \Magedelight\Vendor\Helper\Microsite\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper,
        \Magedelight\Vendor\Helper\Microsite\Image $imageHelper,
        \Magento\Framework\Locale\Resolver $localeResolver,
        array $data = []
    ) {
        $this->micrositeHelper = $micrositeHelper;
        $this->imageHelper = $imageHelper;
        $this->collectionFactory = $collectionFactory;
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    public function getBestSellersCollection($limit = '')
    {
        if (!$limit) {
            $limit = self::LIMIT;
        }

        $collection = $this->collectionFactory->create();

        $collection->getSelect()->join(
            ['mvwd' => 'md_vendor_website_data'],
            "mvwd.vendor_id = main_table.vendor_id AND mvwd.status = ".VendorStatus::VENDOR_STATUS_ACTIVE,
            ['business_name','logo','enable_microsite']
        );

        $collection->getSelect()->joinLeft(
            ['rbvrt' => 'md_vendor_order'],
            "main_table.vendor_id = rbvrt.vendor_id AND rbvrt.status = '".VendorOrder::STATUS_COMPLETE."'",
            ['rbvrt.status']
        );

        $collection->getSelect()->joinLeft(
            ['order_item' => 'sales_order_item'],
            "order_item.order_id = rbvrt.order_id",
            ['order_item.order_id']
        );
        $collection->getSelect()->joinLeft(
            ['prod' => 'catalog_product_entity'],
            "order_item.product_id = prod.entity_id",
            ['sku']
        )->distinct(true);

        $collection->getSelect()
                ->columns('SUM(order_item.qty_ordered) as total')
                ->group('main_table.vendor_id');
        $collection->getSelect()->where('main_table.is_system = 0')->limit($limit);
        $collection->getSelect()->order("SUM(order_item.qty_ordered) DESC");
        return $collection;
    }

    /**
     *
     * @param \Magedelight\Vendor\Model\Vendor $seller
     * @return string
     */
    public function getMicrositeUrl($seller)
    {
        if (!$seller->getEnableMicrosite()) {
            return;
        }
        return $this->micrositeHelper->getVendorMicrositeUrl($seller->getVendorId());
    }

    /**
     *
     * @param \Magedelight\Vendor\Model\Vendor $seller
     * @return string
     */
    public function getSellerLogo($seller)
    {
        if ($seller->getLogo() && $resizedImage = $this->resize($seller->getLogo(), null, 200)) {
            return $resizedImage;
        } else {
            return $this->getViewFileUrl('Magedelight_Vendor::images/small_image.jpg');
        }
    }

    /**
     * @param string $image
     * @param integer $width
     * @param integer $height
     * @return string
     */
    public function resize($image, $width = null, $height = null)
    {
        return $this->imageHelper->resize($image, $width, $height);
    }

    public function getCurrentLocale()
    {
        $currentLocaleCode = $this->localeResolver->getLocale();
        $languageCode = strstr($currentLocaleCode, '_', true);
        return $languageCode;
    }
}
