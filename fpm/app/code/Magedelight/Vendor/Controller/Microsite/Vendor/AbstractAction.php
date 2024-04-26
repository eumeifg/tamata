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
namespace Magedelight\Vendor\Controller\Microsite\Vendor;

abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
    ) {
        parent::__construct($context);
        $this->vendorRepository = $vendorRepository;
    }
    
    /**
     * @return boolean
     */
    public function validVendor()
    {
        $vId = $this->getRequest()->getParam('vid', false);
        if ($vId) {
            try {
                $vendor = $this->vendorRepository->getById($vId);
                if (!$vendor->getId()) {
                    return false;
                } elseif ($vendor->getStatus() != 1) {
                    return false;
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return false;
            }
            return true;
        }
        return false;
    }
}
