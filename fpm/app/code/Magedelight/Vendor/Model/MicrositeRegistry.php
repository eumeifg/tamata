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
namespace Magedelight\Vendor\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Registry for \Magedelight\Vendor\Model\Microsite
 */
class MicrositeRegistry
{
    const REGISTRY_SEPARATOR = ':';

    /**
     * @var MicrositeFactory
     */
    private $micrositeFactory;

    /**
     * @var array
     */
    private $micrositeRegistryById = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param MicrositeFactory $micrositeFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MicrositeFactory $micrositeFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->micrositeFactory = $micrositeFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve Microsite Model from registry given an id
     *
     * @param string $micrositeId
     * @return Microsite
     * @throws NoSuchEntityException
     */
    public function retrieve($micrositeId)
    {
        if (isset($this->micrositeRegistryById[$micrositeId])) {
            return $this->micrositeRegistryById[$micrositeId];
        }
        /** @var Microsite $microsite */

        $microsite = $this->micrositeFactory->create()->load($micrositeId);

        if (!$microsite->getId()) {
            /* microsite does not exist */
            throw NoSuchEntityException::singleField('micrositeId', $micrositeId);
        } else {
            $this->micrositeRegistryById[$micrositeId] = $microsite;
            return $microsite;
        }
    }

    /**
     * Remove instance of the Microsite Model from registry given an id
     *
     * @param int $micrositeId
     * @return void
     */
    public function remove($micrositeId)
    {
        if (isset($this->micrositeRegistryById[$micrositeId])) {
            /** @var Microsite $microsite */
            $microsite = $this->micrositeRegistryById[$micrositeId];
            unset($this->micrositeRegistryById[$micrositeId]);
        }
    }
}
