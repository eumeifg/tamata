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
namespace Magedelight\Catalog\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateMarketplaceStock extends Command
{

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock
     */
    private $stockIndexer;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock
     */
    private $resourceModel;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock $stockIndexer
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock $resourceModel
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock $stockIndexer,
        \Magento\CatalogInventory\Model\ResourceModel\Stock $resourceModel
    ) {
        $this->stockIndexer = $stockIndexer;
        $this->resourceModel = $resourceModel;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('marketplace:stock:update')
            ->setDescription(
                'Checks vendor offers for existing stock and updates their status accordingly.'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updateSetOutOfStock();
        $this->stockIndexer->executeFull();
        $output->writeln('<info>Stock successfully updated.<info>');
    }

    /**
     *
     * @return void
     */
    public function updateSetOutOfStock()
    {
        $connection = $this->resourceModel->getConnection();
        $values = ['is_in_stock' => 0, 'qty' => 0];

        $select = $connection->select()->from(
            $this->resourceModel->getTable('catalog_product_entity'),
            'entity_id'
        )->where('entity_id NOT IN(' . new \Zend_Db_Expr(
            'SELECT marketplace_product_id from md_vendor_product where md_vendor_product.is_deleted = 0'
        ) . ')')->order('entity_id');

        $where = sprintf(
            ' is_in_stock = 1' .
            ' AND qty > 0' .
            ' AND product_id IN (%1$s)',
            $select->assemble()
        );

        $connection->update($this->resourceModel->getTable('cataloginventory_stock_item'), $values, $where);
    }
}
