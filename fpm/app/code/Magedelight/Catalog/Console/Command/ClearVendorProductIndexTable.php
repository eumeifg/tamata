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
use Magedelight\Catalog\Model\Indexer\Product\Vendor\Action\Rows;

class ClearVendorProductIndexTable extends Command
{

    /**
     * @var Rows
     */
    private $productVendorIndexerRows;

    /**
     * @param Rows $productVendorIndexerRows
     */
    public function __construct(
        Rows $productVendorIndexerRows
    ) {
        $this->productVendorIndexerRows = $productVendorIndexerRows;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('clear:vendorproduct:index')
            ->setDescription('Truncate/Empty vendor product index table.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->productVendorIndexerRows->clearTemporaryIndexTable();
        $output->writeln('<info>Vendor product index table successfully truncated.<info>');
    }
}
