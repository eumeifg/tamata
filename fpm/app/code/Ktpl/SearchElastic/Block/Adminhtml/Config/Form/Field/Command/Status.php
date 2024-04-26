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

namespace Ktpl\SearchElastic\Block\Adminhtml\Config\Form\Field\Command;

use Ktpl\SearchElastic\Block\Adminhtml\Config\Form\Field\Command;

/**
 * Class Status
 *
 * @package Ktpl\SearchElastic\Block\Adminhtml\Config\Form\Field\Command
 */
class Status extends Command
{
    /**
     * Get action name
     *
     * @return string
     */
    public function getAction()
    {
        return 'status';
    }
}
