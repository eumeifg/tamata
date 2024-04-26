<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;

/**
 * Backend block structure reader with ACL support
 */
class Block extends Layout\Reader\Block
{
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\ReaderPool $readerPool,
        InterpreterInterface $argumentInterpreter,
        Condition $conditionReader,
        $scopeType = null
    ) {
        $this->attributes[] = 'acl';
        parent::__construct(
            $helper,
            $argumentParser,
            $readerPool,
            $argumentInterpreter,
            $conditionReader,
            $scopeType
        );
    }
}
