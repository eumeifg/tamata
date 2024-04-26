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

class RefreshMarketplaceStock extends Command
{
    /**
     * @var \Magedelight\Catalog\Model\StockResolver
     */
    private $stockResolver;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock
     */
    private $stockIndexer;

    /**
     * RefreshMarketplaceStock constructor.
     * @param \Magedelight\Catalog\Model\StockResolver $stockResolver
     * @param \Magento\CatalogInventory\Model\Indexer\Stock $stockIndexer
     */
    public function __construct(
        \Magedelight\Catalog\Model\StockResolver $stockResolver,
        \Magento\CatalogInventory\Model\Indexer\Stock $stockIndexer
    ) {
        $this->stockResolver = $stockResolver;
        $this->stockIndexer = $stockIndexer;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('marketplace:stock:refresh')
            ->setDescription('Checks vendor offers for existing stock and updates their status accordingly.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);
        $this->stockResolver->updateMultipleProductStock();
        $this->stockIndexer->executeFull();
        $resultTime = microtime(true) - $startTime;
        $output->writeln(
            '<info>Stock status successfully refreshed in ' . gmdate("H:i:s", $resultTime) . '<info>'
        );
        $output->writeln(
            '<info>It updates 5000 product\'s Status and Quantity at a time. Please execute again for the rest.<info>'
        );
    }
}
