<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Adminhtml;

use Magedelight\Commissions\Api\CategoryCommissionRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Commissions extends Action
{
    /**
     * commission repository
     *
     * @var CategoryCommissionRepositoryInterface
     */
    protected $categoryCommissionRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     *
     * @param Registry $registry
     * @param CategoryCommissionRepositoryInterface $categoryCommissionRepository
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        CategoryCommissionRepositoryInterface $categoryCommissionRepository,
        Context $context
    ) {
        $this->coreRegistry = $registry;
        $this->categoryCommissionRepository = $categoryCommissionRepository;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::manage');
    }

    /**
     * @param bool $commissionId
     * @return \Magedelight\Commissions\Api\Data\CommissionInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initCommission($commissionId = false)
    {
        if (!$commissionId) {
            return null;
        }
        $commission = $this->categoryCommissionRepository->get($commissionId);
        $this->coreRegistry->register('md_commissions_commission', $commission);
        return $commission;
    }
}
