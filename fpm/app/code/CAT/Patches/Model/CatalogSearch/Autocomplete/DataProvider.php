<?php

namespace CAT\Patches\Model\CatalogSearch\Autocomplete;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\Collection;

class DataProvider extends \Magento\CatalogSearch\Model\Autocomplete\DataProvider
{
    /**
     * Query factory
     *
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * Limit
     *
     * @var int
     */
    protected $limit;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ScopeConfig $scopeConfig
    ) {
        parent::__construct($queryFactory, $itemFactory, $scopeConfig);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        $collection = $this->getSuggestCollection();
        if (!empty($this->limit)) {
            $collection->setPageSize($this->limit*5);
            $collection->setCurPage(1)->load();
        }
        
        $query = $this->queryFactory->get()->getQueryText();
        $result = [];
        foreach ($collection as $item) {
            $resultItem = $this->itemFactory->create([
                'title' => $item->getQueryText(),
                'num_results' => $item->getNumResults(),
            ]);
            if ($resultItem->getTitle() == $query) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }
        return ($this->limit) ? array_splice($result, 0, $this->limit) : $result;
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
     */
    private function getSuggestCollection()
    {
        return $this->queryFactory->get()->getSuggestCollection();
    }
}
