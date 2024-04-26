<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface;
use Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class KtplRecentViewProduct extends \Magento\Framework\Model\AbstractModel
{

    protected $ktpl_recentlyviewproductDataFactory;

    protected $dataObjectHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param KtplRecentViewProductInterfaceFactory $ktpl_recentlyviewproductDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Ktpl\Pushnotification\Model\ResourceModel\KtplRecentViewProduct $resource
     * @param \Ktpl\Pushnotification\Model\ResourceModel\KtplRecentViewProduct\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        KtplRecentViewProductInterfaceFactory $ktpl_recentlyviewproductDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplRecentViewProduct $resource,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplRecentViewProduct\Collection $resourceCollection,
        array $data = []
    ) {
        $this->ktpl_recentlyviewproductDataFactory = $ktpl_recentlyviewproductDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve ktpl_recentlyviewproduct model with ktpl_recentlyviewproduct data
     * @return KtplRecentViewProductInterface
     */
    public function getDataModel()
    {
        $ktpl_recentlyviewproductData = $this->getData();

        $ktpl_recentlyviewproductDataObject = $this->ktpl_recentlyviewproductDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $ktpl_recentlyviewproductDataObject,
            $ktpl_recentlyviewproductData,
            KtplRecentViewProductInterface::class
        );

        return $ktpl_recentlyviewproductDataObject;
    }
}

