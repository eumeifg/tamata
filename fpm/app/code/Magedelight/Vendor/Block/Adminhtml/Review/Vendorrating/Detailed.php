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

class Detailed extends \Magento\Backend\Block\Template
{
    private $_registry;
   
    protected $_vendorfrontratingtypeFactory;
    
    protected $_template = 'Magedelight_Vendor::review/detailed.phtml';
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magedelight\Vendor\Model\VendorfrontratingtypeFactory $vendorfrontratingtypeFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_vendorfrontratingtypeFactory = $vendorfrontratingtypeFactory;
        $this->_registry = $registry;
    }
    protected function _construct()
    {
        parent::_construct();
    }
    public function getReviewData()
    {
        $data = $this->_registry->registry('vendorrating');
        $ratmodel =  $this->_vendorfrontratingtypeFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('vendor_rating_id', $data->getVendorRatingId());
        
        foreach ($ratmodel as $ratmodelvendor) {
                $result['vendorrating'][] =  $ratmodelvendor->getData();
        }
        return $result;
    }
}
