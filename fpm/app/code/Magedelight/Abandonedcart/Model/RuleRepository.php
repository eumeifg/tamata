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

namespace Magedelight\Abandonedcart\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magedelight\Abandonedcart\Api\RuleRepositoryInterface;
use Magedelight\Abandonedcart\Api\Data\RuleInterface;
use Magedelight\Abandonedcart\Api\Data\RuleInterfaceFactory;
use Magedelight\Abandonedcart\Api\Data\RuleSearchResultsInterfaceFactory;
use Magedelight\Abandonedcart\Model\ResourceModel\Rule as ResourceData;
use Magedelight\Abandonedcart\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var ResourceData
     */
    protected $resource;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var RuleInterfaceFactory
     */
    protected $ruleInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(
        ResourceData $resource,
        RuleCollectionFactory $ruleCollectionFactory,
        RuleSearchResultsInterfaceFactory $ruleSearchResultsInterfaceFactory,
        RuleInterfaceFactory $ruleInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->searchResultsFactory = $ruleSearchResultsInterfaceFactory;
        $this->ruleInterfaceFactory = $ruleInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param RuleInterface $data
     * @return RuleInterface
     * @throws CouldNotSaveException
     */
    public function save(RuleInterface $data)
    {
        try {
            /** @var RuleInterface|\Magento\Framework\Model\AbstractModel $data */
            $this->resource->save($data);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return $data;
    }

    /**
     * Get data record
     *
     * @param $ruleId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($ruleId)
    {
        if (!isset($this->instances[$ruleId])) {
            /** @var \Magedelight\Abandonedcart\Api\Data\RuleInterface|\Magento\Framework\Model\AbstractModel $data */
            $data = $this->ruleInterfaceFactory->create();
            $this->resource->load($data, $ruleId);
            if (!$data->getId()) {
                throw new NoSuchEntityException(__('Requested data doesn\'t exist'));
            }
            $this->instances[$ruleId] = $data;
        }
        return $this->instances[$ruleId];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Abandonedcart\Api\Data\DataSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Abandonedcart\Api\Data\DataSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magedelight\Abandonedcart\Model\ResourceModel\Rule\Collection $collection */
        $collection = $this->ruleCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            $field = 'abandoned_cart_rule_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $data = [];
        foreach ($collection as $datum) {
            $dataDataObject = $this->ruleInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray($dataDataObject, $datum->getData(), RuleInterface::class);
            $data[] = $dataDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($data);
    }

    /**
     * @param RuleInterface $data
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(RuleInterface $data)
    {
        /** @var \Magedelight\Abandonedcart\Api\Data\RuleInterface|\Magento\Framework\Model\AbstractModel $data */
        $id = $data->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($data);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove data %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * @param $ruleId
     * @return bool
     */
    public function deleteById($ruleId)
    {
        $data = $this->getById($ruleId);
        return $this->delete($data);
    }
}
