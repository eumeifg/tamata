<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magedelight\Abandonedcart\Api\RuleRepositoryInterface;

abstract class Rule extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ACTION_RESOURCE = 'Magedelight_Abandonedcart::rule';

    /**
     * Data repository
     *
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Vendor\Rules\Model\RuleFactory
     */
    public $ruleFactory;
    
    /**
     * Result Forward Factory
     *
     * @var ForwardFactory
     */

    protected $resultForwardFactory;

    /**
     * Data constructor.
     *
     * @param Registry $registry
     * @param RuleRepositoryInterface $ruleRepository
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Vendor\Rules\Model\RuleFactory $ruleFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        RuleRepositoryInterface $ruleRepository,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magedelight\Abandonedcart\Model\RuleFactory $ruleFactory,
        Context $context
    ) {
        $this->coreRegistry         = $registry;
        $this->ruleRepository       = $ruleRepository;
        $this->resultPageFactory    = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->ruleFactory          = $ruleFactory;
        parent::__construct($context);
    }
}
