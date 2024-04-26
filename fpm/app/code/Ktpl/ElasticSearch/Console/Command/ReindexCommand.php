<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State as AppState;

/**
 * Class ReindexCommand
 *
 * @package Ktpl\ElasticSearch\Console\Command
 */
class ReindexCommand extends Command
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ReindexCommand constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param AppState $appState
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        AppState $appState,
        ObjectManagerInterface $objectManager
    )
    {
        $this->indexRepository = $indexRepository;
        $this->appState = $appState;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ktpl:search:reindex')
            ->setDescription('Reindex all search indexes')
            ->setDefinition([]);

        $this->addOption('index', 'i', InputOption::VALUE_REQUIRED, 'Reindex particular index');
        $this->addOption('store', 's', InputOption::VALUE_REQUIRED, 'Reindex particular store');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ts = microtime(true);

        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Exception $e) {
        }

        $collection = $this->indexRepository->getCollection()
            ->addFieldToFilter('is_active', 1);

        /** @var IndexInterface $index */
        foreach ($collection as $index) {
            $output->write($index->getTitle() . ' [' . $index->getIdentifier() . ']....');

            if ($input->getOption('index') && $input->getOption('index') !== $index->getIdentifier()) {
                $output->writeln('skip');
                continue;
            }

            try {
                /** @var \Ktpl\ElasticSearch\Model\Index\AbstractIndex $instance */
                $instance = $this->indexRepository->getInstance($index);

                $instance->reindexAll($input->getOption('store'));

                $output->writeln("<info>Done</info>");
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
            }
        }

        $output->writeln(round(microtime(true) - $ts, 0) . ' sec');
    }
}
