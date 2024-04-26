<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Ui\Index\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\Index\Form
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * DataProvider constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param UiComponentFactory $uiComponentFactory
     * @param ContextInterface $context
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        UiComponentFactory $uiComponentFactory,
        ContextInterface $context,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->context = $context;

        $this->indexRepository = $indexRepository;
        $this->collection = $this->indexRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get Meta data
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        $id = $this->context->getRequestParam($this->getRequestFieldName(), null);
        if ($id) {
            $index = $this->indexRepository->get($id);

            $identifier = 'search_index_form_' . $index->getIdentifier();

            if (in_array($identifier, [
                'search_index_form_catalogsearch_fulltext',
                'search_index_form_magento_catalog_attribute',
                'search_index_form_magento_cms_page',
                'search_index_form_external_wordpress_post',
                'search_index_form_blackbird_contentmanager_content',
            ])) {
                $component = $this->uiComponentFactory->create($identifier);

                return ['props' => $this->prepareComponent($component)];
            }

        }

        return parent::getMeta();
    }

    /**
     * Prepare component
     *
     * @param UiComponentInterface $component
     * @return array
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        $data = [];
        foreach ($component->getChildComponents() as $child) {
            $data['children'][] = $this->prepareComponent($child);
        }
        $component->prepare();
        $data['arguments']['data'] = $component->getData();

        return $data;
    }

    /**
     * Get indexed data
     * @return array
     */
    public function getData()
    {
        $result = [];

        foreach ($this->indexRepository->getCollection() as $index) {
            $instance = $this->indexRepository->getInstance($index);
            $attributes = $instance->getAttributeWeights();

            if (count($attributes) == 0) {
                $attributes['empty'] = 1; // for correct js type casting
            }

            $properties = $index->getProperties();
            if (count($properties) == 0) {
                $properties['empty'] = 1; // for correct js type casting
            }

            $data = [
                IndexInterface::ID => $index->getId(),
                IndexInterface::TITLE => $index->getTitle(),
                IndexInterface::IDENTIFIER => $index->getIdentifier(),
                IndexInterface::IS_ACTIVE => $index->getIsActive(),
                IndexInterface::POSITION => $index->getPosition(),
                'attributes' => $attributes,
                'properties' => $properties,
            ];

            $result[$index->getId()] = $data;
        }

        return $result;
    }
}
