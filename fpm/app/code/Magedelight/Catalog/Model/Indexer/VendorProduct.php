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
namespace Magedelight\Catalog\Model\Indexer;

class VendorProduct implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Row
     */
    protected $_productVendorIndexerRow;

    /**
     * @var \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Rows
     */
    protected $_productVendorIndexerRows;

    /**
     * @var \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Full
     */
    protected $_productVendorIndexerFull;

    /**
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Row $productVendorIndexerRow
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Rows $productVendorIndexerRows
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Full $productVendorIndexerFull
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Row $productVendorIndexerRow,
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Rows $productVendorIndexerRows,
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Full $productVendorIndexerFull,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_productVendorIndexerRow  = $productVendorIndexerRow;
        $this->_productVendorIndexerRows = $productVendorIndexerRows;
        $this->_productVendorIndexerFull = $productVendorIndexerFull;
        $this->logger = $logger;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->logger->addDebug('some text or variable1');
        $this->_productVendorIndexerRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->logger->addDebug('variable2');
        $this->_productVendorIndexerFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->logger->addDebug('some text or variable3');
        $this->_productVendorIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->logger->addDebug('some text or variable4');
        $this->_productVendorIndexerRow->execute($id);
    }
}
