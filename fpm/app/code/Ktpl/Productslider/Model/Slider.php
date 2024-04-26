<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Api\DataObjectHelper;
use Ktpl\Productslider\Api\Data\ProductsliderInterface;
use Ktpl\Productslider\Api\Data\ProductsliderInterfaceFactory;

/**
 * @method Slider setName($name)
 * @method Slider setStoreViews($storeViews)
 * @method Slider setActiveFrom($activeFrom)
 * @method Slider setActiveTo($activeTo)
 * @method Slider setStatus($status)
 * @method Slider setSerializedData($serializedData)
 * @method mixed getName()
 * @method mixed getStoreViews()
 * @method mixed getActiveFrom()
 * @method mixed getActiveTo()
 * @method mixed getStatus()
 * @method mixed getSerializedData()
 * @method Slider setCreatedAt(string $createdAt)
 * @method string getCreatedAt()
 * @method Slider setUpdatedAt(string $updatedAt)
 * @method string getUpdatedAt()
 */
class Slider extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'ktpl_productslider_slider';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'ktpl_productslider_slider';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ktpl_productslider_slider';

    protected $dataObjectHelper;

    protected $productsliderDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ProductsliderInterfaceFactory $productsliderDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Ktpl\Productslider\Model\ResourceModel\Productslider $resource
     * @param \Ktpl\Productslider\Model\ResourceModel\Productslider\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ProductsliderInterfaceFactory $productsliderDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Ktpl\Productslider\Model\ResourceModel\Slider $resource,
        \Ktpl\Productslider\Model\ResourceModel\Slider\Collection $resourceCollection,
        array $data = []
    ) {
        $this->productsliderDataFactory = $productsliderDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param \Magento\Framework\DataObject $dataObject
     *
     * @return array|bool
     * @throws \Exception
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result   = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate   = $dataObject->getToDate();
        }

        if ($fromDate && $toDate) {
            $fromDate = new \DateTime($fromDate);
            $toDate   = new \DateTime($toDate);

            if ($fromDate > $toDate) {
                $result[] = __('End Date must follow Start Date.');
            }
        }

        return !empty($result) ? $result : true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Ktpl\Productslider\Model\ResourceModel\Slider::class);
    }

    /**
     * Retrieve productslider model with productslider data
     * @return ProductsliderInterface
     */
    public function getDataModel()
    {
        $productsliderData = $this->getData();

        $productsliderDataObject = $this->productsliderDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $productsliderDataObject,
            $productsliderData,
            ProductsliderInterface::class
        );

        return $productsliderDataObject;
    }
}
