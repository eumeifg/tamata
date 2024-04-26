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

namespace Ktpl\ElasticSearch\Index\Magento\Catalog\Attribute;

use Magento\Eav\Model\Config as EavConfig;
use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Ktpl\ElasticSearch\Model\Index\Context;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Magento\Catalog\Attribute
 */
class Index extends AbstractIndex
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * Index constructor.
     *
     * @param EavConfig $eavConfig
     * @param Context $context
     * @param array $dataMappers
     */
    public function __construct(
        EavConfig $eavConfig,
        Context $context,
        $dataMappers
    )
    {
        $this->eavConfig = $eavConfig;

        parent::__construct($context, $dataMappers);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'Magento / Attribute';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'magento_catalog_attribute';
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'label' => __('Attribute value (option)'),
        ];
    }

    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'value';
    }

    /**
     * Build Search Collection
     *
     * @return Collection|Collection\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedExceptionbuildSearchCollection
     */
    public function buildSearchCollection()
    {
        $ids = $this->context->getSearcher()->getMatchedIds();

        $collection = new Collection(new EntityFactory($this->context->getObjectManager()));

        $attribute = $this->eavConfig->getAttribute(
            'catalog_product',
            $this->getModel()->getProperty('attribute')
        );

        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                if (in_array($option['value'], $ids)) {
                    $collection->addItem(
                        new DataObject($option)
                    );
                }
            }
        }

        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|Collection|Collection\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = new Collection(new EntityFactory($this->context->getObjectManager()));

        if ($lastEntityId) {
            return $collection;
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getModel()->getProperty('attribute'));
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                $collection->addItem(
                    new DataObject($option)
                );
            }
        }

        return $collection;
    }
}
