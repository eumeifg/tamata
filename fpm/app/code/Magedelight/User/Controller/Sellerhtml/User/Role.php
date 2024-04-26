<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Controller\Sellerhtml\User;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

abstract class Role extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magedelight\Authorization\Model\RulesFactory
     */
    protected $rulesFactory;

    /**
     * @var \Magedelight\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magedelight\Authorization\Model\RoleFactory
     */
    protected $roleFactory;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Authorization\Model\RoleFactory $roleFactory
     * @param \Magedelight\User\Model\UserFactory $userFactory
     * @param \Magedelight\Authorization\Model\RulesFactory $rulesFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Authorization\Model\RoleFactory $roleFactory,
        \Magedelight\User\Model\UserFactory $userFactory,
        \Magedelight\Authorization\Model\RulesFactory $rulesFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magedelight\Vendor\Helper\Data $vendorHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        $this->coreRegistry = $coreRegistry;
        $this->roleFactory = $roleFactory;
        $this->userFactory = $userFactory;
        $this->rulesFactory = $rulesFactory;
        $this->filterManager = $filterManager;
        $this->vendorHelper = $vendorHelper;
        parent::__construct($context);
    }
    
    /**
     * Initialize role model by passed parameter in request
     *
     * @param string $requestVariable
     * @return \Magedelight\User\Model\Role
     */
    protected function _initRole($requestVariable = 'role_id')
    {
        $role = $this->roleFactory->create()->load($this->getRequest()->getParam($requestVariable));
        $this->coreRegistry->register('current_role', $role);
        return $role;
    }
}
