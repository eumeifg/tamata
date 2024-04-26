<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\ResourceModel\Indexer;

use Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\VendorProductInterface;

class VendorProductFactory
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Default Stock Indexer resource model name
     *
     * @var string
     */
    protected $_defaultIndexer = \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendors::class;

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * Create new indexer object
     *
     * @param string $indexerClassName
     * @param array $data
     * @return VendorProductInterface
     * @throws \InvalidArgumentException
     */
    public function create($indexerClassName = '', array $data = [])
    {
        //  $this->logger->addDebug('createVenProFac');

        if (empty($indexerClassName)) {
            $indexerClassName = $this->_defaultIndexer;
        }
        $indexer = $this->_objectManager->create($indexerClassName, $data);
        if (false == $indexer instanceof VendorProductInterface) {
            throw new \InvalidArgumentException(
                $indexerClassName .
                ' doesn\'t implement \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\VendorProductInterface'
            );
        }
        return $indexer;
    }
}
