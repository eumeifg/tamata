<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface;
use Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;


class KtplDevicetokens extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'ktpl_devicetokens';
    protected $ktpl_devicetokensDataFactory;

    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param KtplDevicetokensInterfaceFactory $ktpl_devicetokensDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens $resource
     * @param \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        KtplDevicetokensInterfaceFactory $ktpl_devicetokensDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens $resource,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\Collection $resourceCollection,
        array $data = []
    ) {
        $this->ktpl_devicetokensDataFactory = $ktpl_devicetokensDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve ktpl_devicetokens model with ktpl_devicetokens data
     * @return KtplDevicetokensInterface
     */
    public function getDataModel()
    {
        $ktpl_devicetokensData = $this->getData();

        $ktpl_devicetokensDataObject = $this->ktpl_devicetokensDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $ktpl_devicetokensDataObject,
            $ktpl_devicetokensData,
            KtplDevicetokensInterface::class
        );

        return $ktpl_devicetokensDataObject;
    }
}

