<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\VendorPromotion\Observer;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\RuleRepository;

class RemoveVendorFromCartPriceRule implements ObserverInterface
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var RuleRepository
     */
    private $ruleRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     *
     * @param RuleFactory $ruleFactory
     * @param RuleRepository $ruleRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RuleFactory $ruleFactory,
        RuleRepository $ruleRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendorIds = (array) $observer->getData('vendor_ids');
        foreach ($vendorIds as $vendorId) {
            $filter = $this->filterBuilder->setField('vendor_id')->setConditionType('like')->setValue('%' . $vendorId . '%')->create();
            $this->searchCriteriaBuilder->addFilters([$filter]);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $ruleData  = $this->ruleRepository->getList($searchCriteria)->getItems();
            foreach ($ruleData as $data) {
                $record = $this->ruleFactory->create()->load($data->getRuleId());
                $getVendorIds = $record->getData('vendor_id');
                if (strpos($getVendorIds, ',')) {
                    $ids = explode(',', $getVendorIds);
                } else {
                    $ids = $getVendorIds;
                }
                if (!is_string($ids) && $key = array_search($vendorId, $ids)) {
                    unset($ids[$key]);
                    $updatedVendorIds = implode(',', $ids);
                } else {
                    $updatedVendorIds = null;
                }
                $record->setData('vendor_id', $updatedVendorIds)->save();
                $record->unsetData();
            }
        }
    }
}
