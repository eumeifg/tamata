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

class ProductWebsite implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Product Website repository
     *
     * @var \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface
     */
    protected $ProductWebsiteRepository;

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
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $ProductWebsiteRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $ProductWebsiteRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->ProductWebsiteRepository = $ProductWebsiteRepository;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->filterBuilder            = $filterBuilder;
    }

    /**
     * Retrieve all Product Websites as an option array
     *
     * @return array
     * @throws StateException
     */
    public function getAllOptions()
    {
        if (empty($this->options)) {
            $options = [];
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->ProductWebsiteRepository->getList($searchCriteria);
            foreach ($searchResults->getItems() as $ProductWebsite) {
                $options[] = [
                    'value' => $ProductWebsite->getProductWebsiteId(),
                    'label' => $ProductWebsite->getVendor_id(),
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
