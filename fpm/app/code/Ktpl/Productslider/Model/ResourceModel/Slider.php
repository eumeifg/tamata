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

namespace Ktpl\Productslider\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Ktpl\Productslider\Helper\Data;

/**
 * Class Slider
 * @package Ktpl\Productslider\Model\ResourceModel
 */
class Slider extends AbstractDb
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Slider constructor.
     * @param Context $context
     * @param Data $helper
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Data $helper,
        $connectionName = null
    ) {
        $this->helper = $helper;

        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ktpl_productslider_slider', 'slider_id');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $storeIds = $object->getStoreIds();
        if (is_array($storeIds)) {
            $object->setStoreIds(implode(',', $storeIds));
        }

        $groupIds = $object->getCustomerGroupIds();
        if (is_array($groupIds)) {
            $object->setCustomerGroupIds(implode(',', $groupIds));
        }

        $displayAddition = $object->getDisplayAdditional();
        if (is_array($displayAddition)) {
            $object->setDisplayAdditional(implode(',', $displayAddition));
        }

        $responsiveItems = $object->getResponsiveItems();
        if ($responsiveItems && is_array($responsiveItems)) {
            $object->setResponsiveItems($this->helper->serialize($responsiveItems));
        } else {
            $object->setResponsiveItems($this->helper->serialize([]));
        }

        $brandVendor = $object->getBrandVendor();
        if ($brandVendor && is_array($brandVendor)) {
            $object->setBrandVendor($this->helper->serialize($brandVendor));
        } else {
            $object->setBrandVendor($this->helper->serialize([]));
        }

        return parent::_beforeSave($object);
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        if ($object->getResponsiveItems()!==null) {
            $object->setResponsiveItems($this->helper->unserialize($object->getResponsiveItems()));
        } else {
            $object->setResponsiveItems(null);
        }

        if ($object->getBrandVendor()!==null) {
            $object->setBrandVendor($this->helper->unserialize($object->getBrandVendor()));
        } else {
            $object->setBrandVendor(null);
        }

        return $this;
    }
}
