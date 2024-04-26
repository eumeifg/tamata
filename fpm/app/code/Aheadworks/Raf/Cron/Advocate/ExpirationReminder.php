<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Cron\Advocate;

use Aheadworks\Raf\Api\AdvocateExpirationManagementInterface;
use Aheadworks\Raf\Cron\Management;
use Aheadworks\Raf\Model\Flag;
use Psr\Log\LoggerInterface;

/**
 * Class ExpirationReminder
 *
 * @package Aheadworks\Raf\Cron\Advocate
 */
class ExpirationReminder
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Management
     */
    private $cronManagement;

    /**
     * @var AdvocateExpirationManagementInterface
     */
    private $advocateExpirationManagement;

    /**
     * @param LoggerInterface $logger
     * @param Management $cronManagement
     * @param AdvocateExpirationManagementInterface $advocateExpirationManagement
     */
    public function __construct(
        LoggerInterface $logger,
        Management $cronManagement,
        AdvocateExpirationManagementInterface $advocateExpirationManagement
    ) {
        $this->logger = $logger;
        $this->cronManagement = $cronManagement;
        $this->advocateExpirationManagement = $advocateExpirationManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->cronManagement->isLocked(Flag::AW_RAF_ADVOCATE_EXPIRATION_REMINDER_LAST_EXEC_TIME)) {
            try {
                $this->advocateExpirationManagement->sendExpirationReminder();
            } catch (\LogicException $e) {
                $this->logger->error($e);
            }
            $this->cronManagement->setFlagData(Flag::AW_RAF_ADVOCATE_EXPIRATION_REMINDER_LAST_EXEC_TIME);
        }
    }
}
