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
namespace Magedelight\Backend\Model\View\Layout;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\Condition\ConditionFactory;

/**
 * Pool of generators for structural elements
 */
class GeneratorPool extends \Magento\Framework\View\Layout\GeneratorPool
{
    /**
     * @var Filter\Acl
     */
    protected $aclFilter;

    /**
     * @param ScheduledStructure\Helper $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Psr\Log\LoggerInterface $logger
     * @param Filter\Acl $aclFilter
     * @param array $generators
     */
    public function __construct(
        ScheduledStructure\Helper $helper,
        ConditionFactory $conditionFactory,
        \Psr\Log\LoggerInterface $logger,
        Filter\Acl $aclFilter,
        array $generators = null
    ) {
        $this->aclFilter = $aclFilter;
        parent::__construct(
            $helper,
            $conditionFactory,
            $logger,
            $generators
        );
    }

    /**
     * Build structure that is based on scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @return $this
     */
    protected function buildStructure(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        parent::buildStructure($scheduledStructure, $structure);
        $this->aclFilter->filterAclElements($scheduledStructure, $structure);
        return $this;
    }
}
