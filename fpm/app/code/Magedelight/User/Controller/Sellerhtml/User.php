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
namespace Magedelight\User\Controller\Sellerhtml;

abstract class User extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Model\VendorRepository
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Authorization level of a basic vendor session
     *
     * @see _isAllowed()
     */
    const VENDOR_RESOURCE = 'Magedelight_Vendor::main';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * User model factory
     *
     * @var \Magedelight\User\Model\UserFactory
     */
    protected $userFactory;
    
    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\User\Model\UserFactory $userFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magedelight\Vendor\Model\VendorRepository $vendorRepository
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\User\Model\UserFactory $userFactory,
        \Magedelight\Vendor\Model\Design $design,
        \Magedelight\Vendor\Model\VendorRepository $vendorRepository,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->userFactory = $userFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        $this->vendorRepository = $vendorRepository;
        $this->vendorHelper = $vendorHelper;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        return $this;
    }

    /**
     * Retrieve well-formed admin user data from the form input
     *
     * @param array $data
     * @return array
     */
    protected function _getAdminUserData(array $data)
    {
        if (isset($data['password']) && $data['password'] === '') {
            unset($data['password']);
        }
        if (!isset($data['password'])
            && isset($data['password_confirmation'])
            && $data['password_confirmation'] === ''
        ) {
            unset($data['password_confirmation']);
        }
        return $data;
    }
    
    protected function _initVendor()
    {
        $vendorId = (int) $this->getRequest()->getParam('user_id');
        
        /** @var \Magedelight\Vendor\Api\Data\VendorInterface $vendor */
        $vendor = $this->vendorRepository->getById($vendorId);
        $user = $this->userFactory->create()->load($vendorId);
        $vendor->setData('roles', $user->getRoleId());
        $this->coreRegistry->register('permissions_user', $vendor);
        return $vendor;
    }
}
