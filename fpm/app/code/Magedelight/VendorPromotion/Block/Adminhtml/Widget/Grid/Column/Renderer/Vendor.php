<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magento\Framework\Registry;

/**
 * Description of VendorColumn
 *
 * @author Rocket Bazaar Core Team
 */
class Vendor extends AbstractRenderer
{

    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var vendor
     */
    protected $vendor;
    
    /**
     * Manufacturer constructor.
     * @param AttributeFactory $attributeFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        Context $context,
        array $data = []
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
    
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        // Get default value:
        $value = parent::_getValue($row);
        $returnvalue = [];
        if ($value != '' && $value != null) {
            $vendorIds = explode(',', $value);
            foreach ($vendorIds as $vendorId) {
                $vendor = $this->vendorFactory->create();
                $vendor->load($vendorId);
                if ($vendor->getId()) {
                    $returnvalue[]  = $vendor->getName();
                }
            }
            return implode('<br/>', $returnvalue);
        }
        return parent::_getValue($row);
    }
}
