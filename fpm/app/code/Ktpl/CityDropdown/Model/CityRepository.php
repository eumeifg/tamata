<?php

namespace Ktpl\CityDropdown\Model;

use Ktpl\CityDropdown\Api\Data\CityInterface;
use Ktpl\CityDropdown\Model\ResourceModel\Collection\Collection as CityResourceModel;
use Ktpl\CityDropdown\Api\CityRepositoryInterface;
use Magento\Framework\Exception\LocalizedException as Exception;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Ktpl\CityDropdown\Model\ResourceModel\Collection\Collection;
use Ktpl\CityDropdown\Model\ResourceModel\Collection\CollectionFactory;
use Ktpl\CityDropdown\Api\Data\CitySearchResultInterfaceFactory;

/**
 * Class CityRepository
 * @package Ktpl\CityDropdown\Model
 */
class CityRepository implements CityRepositoryInterface
{
    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var CityResourceModel
     */
    private $cityResourceModel;

    /**
     * @var CityInterface
     */
    private $cityInterface;

    /**
     * @var CityFactory
     */
    private $cityFactory;

    private $citySearchResultInterfaceFactory;

    private $collectionFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * CityRepository constructor.
     * @param CityResourceModel $cityResourceModel
     * @param CityInterface $cityInterface
     * @param CityFactory $cityFactory
     * @param CollectionFactory $collectionFactory
     * @param CitySearchResultInterfaceFactory $citySearchResultInterfaceFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CityResourceModel $cityResourceModel,
        CityInterface $cityInterface,
        CityFactory $cityFactory,
        ManagerInterface $messageManager,
        CollectionFactory $collectionFactory,
        CitySearchResultInterfaceFactory $citySearchResultInterfaceFactory
    ) {
        $this->citySearchResultInterfaceFactory = $citySearchResultInterfaceFactory;
        $this->cityResourceModel = $cityResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->cityInterface = $cityInterface;
        $this->cityFactory = $cityFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param CityInterface $cityInterface
     * @return CityInterface
     * @throws \Exception
     */
    public function save(CityInterface $cityInterface)
    {

        try {
            $this->cityResourceModel->save($cityInterface);
        } catch (Exception $e) {
            $this->messageManager
                ->addExceptionMessage(
                    $e,
                    'There was a error while saving the city ' . $e->getMessage()
                );
        }

        return $cityInterface;
    }

    /**
     * @inheritdoc
     */
    public function getById($countryId)
    {
        $cityCollection = $this->cityResourceModel->addFieldToFilter('entity_id',$countryId)->getData();
        return $cityCollection;
    }

    /**
     * @param cityInterface $cityInterface
     * @return bool
     * @throws \Exception
     */
    public function delete(CityInterface $cityInterface)
    {
        $id = $cityInterface->getId();
        try {
            unset($this->instances[$id]);
            $this->cityResourceModel->delete($cityInterface);
        } catch (Exception $e) {
            $this->messageManager
                ->addExceptionMessage($e, 'There was a error while deleting the city');
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * @param $cityId
     * @return bool
     * @throws \Exception
     */
    public function deleteById($cityId)
    {
        $city = $this->getById($cityId);
        return $this->delete($city);
    }

    /**
     * @param CityInterface $cityInterface
     * @return bool
     * @throws \Exception
     */
    public function saveAndDelete(CityInterface $cityInterface)
    {
        $cityInterface->setData(Data::ACTION, Data::DELETE);
        $this->save($cityInterface);
        return true;
    }

    /**
     * @param $cityId
     * @return bool
     * @throws \Exception
     */
    public function saveAndDeleteById($cityId)
    {
        $city = $this->getById($cityId);
        return $this->saveAndDelete($city);
    }


    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();
        return $this->buildSearchResult($searchCriteria, $collection);
    }

    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->citySearchResultInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getCityByCountryId($countryId)
    {
        $cityCollection = $this->cityResourceModel->addFieldToFilter('country_id',$countryId)->getData();
        return $cityCollection;
    }
}