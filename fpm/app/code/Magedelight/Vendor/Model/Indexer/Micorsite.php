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
namespace Magedelight\Vendor\Model\Indexer;

class Micorsite implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{

    /**
     * @var \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Row
     */
    protected $_micrositeVendorIndexerRow;

    /**
     * @var \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Rows
     */
    protected $_micrositeVendorIndexerRows;

    /**
     * @var \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Full
     */
    protected $_micrositeVendorIndexerFull;

    /**
     * @param \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Row $micrositeVendorIndexerRow
     * @param \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Rows $micrositeVendorIndexerRows
     * @param \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Full $micrositeVendorIndexerFull
     */
    public function __construct(
        \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Row $micrositeVendorIndexerRow,
        \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Rows $micrositeVendorIndexerRows,
        \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Action\Full $micrositeVendorIndexerFull
    ) {
        $this->_micrositeVendorIndexerRow  = $micrositeVendorIndexerRow;
        $this->_micrositeVendorIndexerRows = $micrositeVendorIndexerRows;
        $this->_micrositeVendorIndexerFull = $micrositeVendorIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->_micrositeVendorIndexerRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->_micrositeVendorIndexerFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->_micrositeVendorIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->_micrositeVendorIndexerRow->execute($id);
    }
}
