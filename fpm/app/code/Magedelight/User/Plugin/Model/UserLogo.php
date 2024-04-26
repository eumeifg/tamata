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
namespace Magedelight\User\Plugin\Model;

class UserLogo
{

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    private $vendorRepository;

    /**
     *
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     */
    public function __construct(
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
    ) {
        $this->vendorRepository = $vendorRepository;
    }
    
    public function beforeGetLogoUrl(\Magedelight\Vendor\Block\Sellerhtml\Account\Profile $subject, $logo = '')
    {
        $vendor = $this->vendorRepository->getById($subject->getVendor()->getVendorId());
        if ($vendor && $vendor->getLogo()) {
            $logo = $vendor->getLogo();
        }
        return $logo;
    }
}
