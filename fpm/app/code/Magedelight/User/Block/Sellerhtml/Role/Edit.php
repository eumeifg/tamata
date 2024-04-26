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
namespace Magedelight\User\Block\Sellerhtml\Role;

use Magento\Framework\App\ObjectManager;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole;

/**
 * Description of Edit
 *
 * @author Rocket Bazaar Core Team
 */
class Edit extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * Password input filed name
     */
    const IDENTITY_VERIFICATION_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Integration\Helper\Data
     */
    protected $integrationData;

    /**
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface
     */
    protected $aclResourceProvider;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory
     */
    protected $rulesCollectionFactory;

    /**
     * @var \Magedelight\Authorization\Acl\RootResource
     */
    protected $rootResource;

    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $aclRetriever;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param \Magedelight\Authorization\Acl\RootResource $rootResource
     * @param \Magedelight\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magedelight\User\Model\UserFactory $userFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Magedelight\Authorization\Acl\RootResource $rootResource,
        \Magedelight\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider,
        \Magento\Integration\Helper\Data $integrationData,
        \Magedelight\User\Model\UserFactory $userFactory,
        array $data = []
    ) {
        $this->aclRetriever = $aclRetriever;
        $this->rootResource = $rootResource;
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        $this->aclResourceProvider = $aclResourceProvider;
        $this->integrationData = $integrationData;
        $this->userFactory = $userFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        return $this;
    }
    
    /**
     * Set core registry
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @return void
     * @deprecated
     */
    public function setCoreRegistry(\Magento\Framework\Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }
            
    /**
     * Get core registry
     *
     * @return \Magento\Framework\Registry
     * @deprecated
     */
    public function getCoreRegistry()
    {
        if (!($this->coreRegistry instanceof \Magento\Framework\Registry)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Registry');
        } else {
            return $this->coreRegistry;
        }
    }
    
    public function getRole()
    {
        return $this->getCoreRegistry()->registry('current_role');
    }
    
    /**
     * Check if everything is allowed
     *
     * @return bool
     */
    public function isEverythingAllowed()
    {
        $selectedResources = $this->getSelectedResources();
        $id = $this->rootResource->getId();
        if (is_array($selectedResources)) {
            return in_array($id, $selectedResources);
        } else {
            return true;
        }
    }

    /**
     * Get selected resources
     *
     * @return array|mixed|\string[]
     */
    public function getSelectedResources()
    {
        $selectedResources = $this->getData('selected_resources');
        if (empty($selectedResources)) {
            if (null === $selectedResources) {
                $rid = $this->_request->getParam('role_id', false);
                if ($rid) {
                      $selectedResources = $this->userFactory->create()->getAllowedResourcesByRole($rid);
                } else {
                    $selectedResources = ['Magedelight_Vendor::account_dashboard'];
                }
            }

            $this->setData('selected_resources', $selectedResources);
        }

        return $selectedResources;
    }
    
    /**
     * Get Json Representation of Resource Tree
     *
     * @return array
     */
    public function getTree()
    {
        $children = [];
        $resources = $this->aclResourceProvider->getAclResources();
        foreach ($resources as $resource) {
            if ($resource['id'] == "Magedelight_Vendor::main") {
                foreach ($resource['children'] as $key => $value) {
                    if ($value['id'] == "Magedelight_Vendor::account_dashboard") :
                        unset($resource['children'][$key]);
                    endif;
                }
                $children = $resource['children'];
            }
        }
        $rootArray = $this->integrationData->mapResources($children);
        return $rootArray;
    }
    
    public function getSubmitUrl()
    {
        return $this->getUrl('rbuser/user/role_save');
    }

    public function getLandingPageUrl()
    {
        $allowedResources = null;
        $allowedResources = $this->userFactory->create()->getAllowedResourcesByRole();
    }
}
