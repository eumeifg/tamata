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
namespace MDC\Vendor\Plugin\Controller\Microsite\Vendor;

class Product
{

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;
    
    /**
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     */
    public function __construct(
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
    ) {
        $this->vendorRepository = $vendorRepository;
    }
    
    public function afterValidVendor(\Magedelight\Vendor\Controller\Microsite\Vendor\Product $subject, $result)
    {
        $vId = $subject->getRequest()->getParam('vid', false);
        if ($vId) {
            try {
                $vendor = $this->vendorRepository->getById($vId);
                if ((int)$vendor->getEnableMicrosite() === 0) {
                    return false;
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return false;
            }
            return true;
        }
        return $result;
    }
}
