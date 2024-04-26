<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Adminhtml\Review\Vendorrating;

class Rating extends \Magento\Backend\Block\Template
{
    private $_registry;
    
    protected $_template = 'Magedelight_Vendor::review/rating.phtml';
    
    protected $_ratingFactory;
    
    private $_vendorfrontratingtypeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Framework\Registry $registry,
        \Magedelight\Vendor\Model\Vendorfrontratingtype $vendorfrontratingtypeFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ratingFactory = $ratingFactory;
        $this->_vendorfrontratingtypeFactory = $vendorfrontratingtypeFactory;
        $this->_registry = $registry;
    }
    protected function _construct()
    {
        parent::_construct();
    }
    public function getRating()
    {
        return $this->_ratingFactory->create()->getResourceCollection()->addEntityFilter(
            'vendor'
        )->setPositionOrder()->addRatingPerStoreName(
            $this->_storeManager->getStore()->getId()
        )->setStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->setActiveFilter(
            true
        )->load()->addOptionToItems();
    }
    public function getVendorRetingId()
    {
        $data = $this->_registry->registry('vendorrating');
        return $data->getVendorRatingId();
    }
}
