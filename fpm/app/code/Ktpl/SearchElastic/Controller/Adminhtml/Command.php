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

namespace Ktpl\SearchElastic\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ktpl\SearchElastic\Model\Engine;

/**
 * Class Command
 *
 * @package Ktpl\SearchElastic\Controller\Adminhtml
 */
abstract class Command extends Action
{
    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Command constructor.
     *
     * @param Context $context
     * @param Engine $engine
     */
    public function __construct(
        Context $context,
        Engine $engine
    )
    {
        $this->engine = $engine;
        $this->context = $context;

        parent::__construct($context);
    }
}
