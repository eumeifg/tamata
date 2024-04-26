<?php
namespace CAT\CustomerBalance\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use CAT\CustomerBalance\Model\UpdateStoreCredit;

/**
 * Class UpdateBulkStoreCredit
 * @package CAT\CustomerBalance\Console
 */
class UpdateBulkStoreCredit extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var UpdateStoreCredit
     */
    protected $updateStoreCredit;

    /**
     * UpdateStoreCredit constructor.
     * @param State $state
     * @param UpdateStoreCredit $updateStoreCredit
     * @param string|null $name
     */
    public function __construct(
        State $state,
        UpdateStoreCredit $updateStoreCredit,
        string $name = null
    ) {
        $this->updateStoreCredit = $updateStoreCredit;
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     * Command Description
     */
    public function configure()
    {
        $this->setName('store-credit:bulk-update');
        $this->setDescription('Update Bulk Store Credit');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/bulk_store_credit.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $output->writeln("Store Credit Update Start");
        $updateUrlResponse = $this->updateStoreCredit->updateStoreCredit($logger);
        $output->writeln("Store Credit Update Complete");
    }
}