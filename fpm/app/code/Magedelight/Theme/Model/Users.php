<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Model;

class Users extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magedelight\Theme\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory
     * @param \Magedelight\Theme\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Magedelight\Theme\Helper\Data $helper
    ) {
        $this->objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
    }

    public function getUserEmails($vendorId = null, $resource = null)
    {
        $allowedUsers = [];
        if ($vendorId && $this->helper->isModuleEnabled('Magedelight_User')) {
            $userModel = $this->objectManager->create(\Magedelight\User\Model\User::class);
            $childUsers = $userModel->getVendorChildUsers($vendorId);
            $allowedResources = [];
            foreach ($childUsers as $childUser) {
                $allowedResources = $userModel->getAllowedResourcesByRole($childUser['role_id']);
                if ($allowedResources) {
                    $allowedUsers [] = $this->collectionFactory->create()->addFieldToFilter(
                        'vendor_id',
                        $childUser['vendor_id']
                    )->getFirstItem()->getEmail();
                } elseif (in_array($resource, $allowedResources)) {
                    $allowedUsers [] = $this->collectionFactory->create()->addFieldToFilter(
                        'vendor_id',
                        $childUser['vendor_id']
                    )->getFirstItem()->getEmail();
                }
            }
            return $allowedUsers;
        }
        return [];
    }
}
