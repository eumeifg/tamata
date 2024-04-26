<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CAT\ProductAssign\Block\Adminhtml\Category\AddProduct\Tabs;

use \Magento\VisualMerchandiser\Model\Position\Cache as PositionCache;

/**
 * @api
 * @since 100.0.2
 */
class ParentSkuTab extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getPositionCacheKey()
    {
        return $this->_coreRegistry->registry(PositionCache::POSITION_CACHE_KEY);
    }

    /**
     * @return string
     */
    public function getMassAssignUrl()
    {
        return $this->getUrl(
            'productassign/massassign/index',
            [
                'category_id' => $this->getRequest()->getParam('id'),
                PositionCache::POSITION_CACHE_KEY => $this->getPositionCacheKey(),
                'componentJson' => true
            ]
        );
    }
}
