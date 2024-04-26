<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ManageCommand
 *
 * @package Ktpl\SearchElastic\Console\Command
 */
class ManageCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption('status'),
            new InputOption('reset'),
            new InputOption('get', null, InputOption::VALUE_REQUIRED),
        ];

        $this->setName('ktpl:search-elastic:manage')
            ->setDescription('Elastic engine management')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Ktpl\SearchElastic\Model\Engine $engine */
        $engine = $this->objectManager->create('Ktpl\SearchElastic\Model\Engine');
        if ($input->getOption('status')) {
            $out = '';
            $result = $engine->status($out);
            if ($result) {
                $output->writeln("<comment>$out</comment>");
            } else {
                $output->writeln("<error>$out</error>");
            }
        }

        if ($input->getOption('reset')) {
            $out = '';
            $result = $engine->reset($out);
            if ($result) {
                $output->writeln("<comment>$out</comment>");
            } else {
                $output->writeln("<error>$out</error>");
            }
        }

        if ($input->getOption('get')) {
            $indices = $engine->getClient()->indices()->get(['index' => '*']);
            foreach ($indices as $indexName => $etc) {
                try {
                    $output->writeln($indexName);
                    $result = $engine->getClient()->get([
                        'type' => 'doc',
                        'index' => $indexName,
                        'id' => $input->getOption('get'),
                    ]);
                    print_r($result);
                } catch (\Exception $e) {
                }
            }
        }
    }
}
