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
namespace Magedelight\VendorPromotion\Block\Sellerhtml;

class SalesRule extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Customer\Model\Config\Source\Group
     */
    protected $customerGroup;

    /**
     * @var Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Model\Config\Source\Group\Multiselect $customerGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Model\Config\Source\Group\Multiselect $customerGroup,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        $this->systemStore = $systemStore;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }
    
    /**
     *
     * @return \Magedelight\Backend\Model\Auth\Session
     */
    public function getVendorSession()
    {
        return $this->authSession->getUser();
    }

    /**
     *
     * @return string form action url
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('rbvendor/salesrule/save');
    }
    
    public function getStoreOptionsForForm($empty = false, $all = false)
    {
        return $this->systemStore->getStoreValuesForForm($empty, $all);
    }
    
    public function getCustomerGroups()
    {
        $groups = $this->customerGroup->toOptionArray();
        return  $groups;
    }
}
