<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Source;

class ProductStore implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Product Store repository
     *
     * @var \Magedelight\Catalog\Api\ProductStoreRepositoryInterface
     */
    protected $ProductStoreRepository;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * constructor
     *
     * @param \Magedelight\Catalog\Api\ProductStoreRepositoryInterface $ProductStoreRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magedelight\Catalog\Api\ProductStoreRepositoryInterface $ProductStoreRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->ProductStoreRepository = $ProductStoreRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->filterBuilder          = $filterBuilder;
    }

    /**
     * Retrieve all Product Stores as an option array
     *
     * @return array
     * @throws StateException
     */
    public function getAllOptions()
    {
        if (empty($this->options)) {
            $options = [];
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->ProductStoreRepository->getList($searchCriteria);
            foreach ($searchResults->getItems() as $ProductStore) {
                $options[] = [
                    'value' => $ProductStore->getProductStoreId(),
                    'label' => $ProductStore->getVendor_product_id(),
                ];
            }
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
