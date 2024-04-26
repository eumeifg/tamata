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

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractCommand
 *
 * @package Ktpl\SearchElastic\Console\Command
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var State
     */
    protected $appState;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AbstractCommand constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param State $appState
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        State $appState
    ) {
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $this->objectManager = $objectManager;
        $this->appState = $appState;

        parent::__construct();
    }
}
