<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Authorization
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Authorization\Plugin\Acl\Loader;

use Magento\Framework\Acl;
use Magento\Framework\Acl\AclResource as AclResource;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\AclResourceFactory;

class ResourceLoader implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * Acl resource config
     *
     * @var ProviderInterface $resourceProvider
     */
    protected $_resourceProvider;

    /**
     * Resource factory
     *
     * @var AclResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @param ProviderInterface $resourceProvider
     * @param AclResourceFactory $resourceFactory
     */
    public function __construct(ProviderInterface $resourceProvider, AclResourceFactory $resourceFactory)
    {
        $this->_resourceProvider = $resourceProvider;
        $this->_resourceFactory = $resourceFactory;
    }

    /**
     * Populate ACL with resources from external storage
     *
     * @param Acl $acl
     * @return void
     */
    public function populateAcl(Acl $acl)
    {
        $resources = $this->_resourceProvider->getAclResources();
        unset($resources[0]);
        unset($resources[1]);
        $this->_addResourceTree($acl, $resources, null);
    }

    /**
     * Add list of nodes and their children to acl
     *
     * @param Acl $acl
     * @param array $resources
     * @param AclResource $parent
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _addResourceTree(Acl $acl, array $resources, AclResource $parent = null)
    {
        foreach ($resources as $resourceConfig) {
            if (!isset($resourceConfig['id'])) {
                throw new \InvalidArgumentException('Missing ACL resource identifier');
            }
            /** @var $resource AclResource */
            $resource = $this->_resourceFactory->createResource(['resourceId' => $resourceConfig['id']]);
            $acl->addResource($resource, $parent);
            if (isset($resourceConfig['children'])) {
                $this->_addResourceTree($acl, $resourceConfig['children'], $resource);
            }
        }
    }
}
