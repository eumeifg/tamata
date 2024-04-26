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
namespace Magedelight\Catalog\Block\Adminhtml\Attribute\Edit\Options;

class Descriptions extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magedelight\Catalog\Model\TooltipFactory
     */
    protected $tooltipFactory;

    /**
     * @var string
     */
    protected $_template = 'Magedelight_Catalog::catalog/product/attribute/customtooltip.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Catalog\Model\TooltipFactory $tooltipFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->tooltipFactory = $tooltipFactory;
    }

    /**
     * Retrieve stores collection with default store.
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores());
        }
        return $this->_getData('stores');
    }

    /**
     * Retrieve frontend descriptions of attribute for each store.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDescriptionValues()
    {
        $values = [];
        $storeDescriptions = $this->tooltipFactory->create()
            ->getStoreTooltipsByAttributeId($this->getAttributeObject()->getId());
        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeDescriptions[$store->getId()]) ?
                    $storeDescriptions[$store->getId()] : '';
            }
        }
        return $values;
    }

    /**
     * Retrieve attribute object from registry.
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @codeCoverageIgnore
     */
    private function getAttributeObject()
    {
        return $this->_registry->registry('entity_attribute');
    }

    /**
     * Prepare label for tab.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Manage Tooltip');
    }

    /**
     * Prepare title for tab.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Manage Tooltip');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
