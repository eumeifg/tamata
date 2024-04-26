<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface;
use Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;


class KtplPushnotifications extends \Magento\Framework\Model\AbstractModel
{

    protected $dataObjectHelper;

    protected $ktpl_pushnotificationsDataFactory;

    protected $_eventPrefix = 'ktpl_pushnotification';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param KtplPushnotificationsInterfaceFactory $ktpl_pushnotificationsDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Ktpl\Pushnotification\Model\ResourceModel\KtplPushnotifications $resource
     * @param \Ktpl\Pushnotification\Model\ResourceModel\KtplPushnotifications\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        KtplPushnotificationsInterfaceFactory $ktpl_pushnotificationsDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplPushnotifications $resource,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplPushnotifications\Collection $resourceCollection,
        array $data = []
    ) {
        $this->ktpl_pushnotificationsDataFactory = $ktpl_pushnotificationsDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve ktpl_pushnotifications model with ktpl_pushnotifications data
     * @return KtplPushnotificationsInterface
     */
    public function getDataModel()
    {
        $ktpl_pushnotificationsData = $this->getData();

        $ktpl_pushnotificationsDataObject = $this->ktpl_pushnotificationsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $ktpl_pushnotificationsDataObject,
            $ktpl_pushnotificationsData,
            KtplPushnotificationsInterface::class
        );

        return $ktpl_pushnotificationsDataObject;
    }
}

