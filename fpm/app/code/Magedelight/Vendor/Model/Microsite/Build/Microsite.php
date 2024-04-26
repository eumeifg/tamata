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
namespace Magedelight\Vendor\Model\Microsite\Build;

use Magedelight\Vendor\Api\Data\MicrositeInterface;
use Magedelight\Vendor\Api\MicrositeRepositoryInterface;
use Magedelight\Vendor\Helper\Microsite\Data;

/**
 * Build microsite data
 */
class Microsite extends \Magento\Framework\DataObject
{

    /**
     * @var Data
     */
    protected $micrositeHelper;

    /**
     * @var MicrositeRepositoryInterface
     */
    protected $micrositeRepository;

    /**
     * Microsite constructor.
     * @param MicrositeRepositoryInterface $micrositeRepository
     * @param Data $micrositeHelper
     * @param array $data
     */
    public function __construct(
        MicrositeRepositoryInterface $micrositeRepository,
        Data $micrositeHelper,
        $data = []
    ) {
        $this->micrositeHelper = $micrositeHelper;
        $this->micrositeRepository = $micrositeRepository;
        parent::__construct($data);
    }

    /**
     * @param int $vendorId
     * @param int $storeId
     * @return MicrositeInterface
     * @throws NoSuchEntityException
     */
    public function build($vendorId, $storeId)
    {
        $microsite = $this->micrositeRepository->getByVendorId($vendorId, $storeId);
        $microsite->setBanner($this->micrositeHelper->getMicrositeFileUrl($microsite->getBanner()));
        return $microsite;
    }
}
