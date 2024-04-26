<?php

namespace CAT\SearchPage\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * @api
 * @since 101.0.0
 */
class CategoryIdToName extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'data_id';


    protected $categoryRepository;

    /**
     * CategoryIdToName constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CategoryRepositoryInterface $categoryRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 101.0.0
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            if (!empty($item[static::NAME])) {
                $item[$fieldName] = $this->renderColumnText($item[static::NAME]);
            }
        }

        return $dataSource;
    }

    /**
     * Render column text
     *
     * @param int $attributeSetId
     * @return string
     * @since 101.0.0
     */
    protected function renderColumnText($attributeSetId)
    {
        return $this->categoryRepository->get($attributeSetId)->getName();
    }
}
