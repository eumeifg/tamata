<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Model\Rule\Source;

class CustomerGroupIds extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    protected $customerGroups;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection,
        array $data = []
    ) {
        parent::__construct($data);
        $this->customerGroups = $groupCollection;
        $this->_coreRegistry = $coreRegistry;
    }
     /**
      * Generate list of email templates
      *
      * @return array
      */
    public function toOptionArray()
    {
        $options = $this->customerGroups->toOptionArray();
        
        //array_unshift($options, ['value' => '', 'label' => __('Select Customer Groups')]);
        
        return $options;
    }
}
