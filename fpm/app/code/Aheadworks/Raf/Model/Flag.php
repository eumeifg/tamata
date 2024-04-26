<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model;

use Magento\Framework\Flag as FrameworkFlag;

/**
 * Class Flag
 *
 * @package Aheadworks\Raf\Model
 */
class Flag extends FrameworkFlag
{
    /**#@+
     * Constants for event tickets cron flags
     */
    const AW_RAF_EXPIRE_ADVOCATE_BALANCE_LAST_EXEC_TIME = 'aw_raf_expire_advocate_balance_last_exec_time';
    const AW_RAF_ADVOCATE_EXPIRATION_REMINDER_LAST_EXEC_TIME = 'aw_raf_advocate_expiration_reminder_last_exec_time';
    const AW_RAF_ADVOCATE_TRANSACTION_PROCESSOR_LAST_EXEC_TIME = 'aw_raf_advocate_transaction_processor_last_exec_time';
    /**#@-*/

    /**
     * Setter for flag code
     *
     * @param string $code
     * @return $this
     * @codeCoverageIgnore
     */
    public function setEtFlagCode($code)
    {
        $this->_flagCode = $code;
        return $this;
    }
}
