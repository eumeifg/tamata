<?php

namespace CAT\GiftCard\Model\Rule;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider;
use CAT\GiftCard\Model\ResourceModel\GiftCardRule\Collection;
use CAT\GiftCard\Model\ResourceModel\GiftCardRule\CollectionFactory;
use CAT\GiftCard\Model\GiftCardRule;

/**
 * Class DataProvider
 * @package CAT\GiftCard\Model\Rule
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var DataPersistorInterface|mixed
     */
    protected $dataPersistor;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $meta
     * @param array $data
     * @param DataPersistorInterface|null $dataPersistor
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        //ValueProvider $metadataValueProvider,
        array $meta = [],
        array $data = [],
        DataPersistorInterface $dataPersistor = null
    ) {
        $this->collection = $collectionFactory->create();
        $this->coreRegistry = $registry;
//        $this->metadataValueProvider = $metadataValueProvider;
//        $meta = array_replace_recursive($this->getMetadataValues(), $meta);
        $this->dataPersistor = $dataPersistor ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
                DataPersistorInterface::class
            );
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /*public function getMetadataValues() {
        $rule = $this->coreRegistry->registry(\CAT\Giftcard\Model\RegistryConstants::CURRENT_GIFT_CARD_RULE);
        return $this->metadataValueProvider->getMetadataValues($rule);
    }*/

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var GiftCardRule $rule */
        foreach ($items as $rule) {
            $rule->load($rule->getId());
            //$rule->setDiscountAmount($rule->getDiscountAmount() * 1);
            //$rule->setDiscountQty($rule->getDiscountQty() * 1);

            $this->loadedData[$rule->getId()] = $rule->getData();
        }
        $data = $this->dataPersistor->get('giftcard_rule');
        // echo "<pre>"; print_r($data); echo "</pre>"; die('=====>');
        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('giftcard_rule');
        }

        //echo "<pre>"; print_r($this->loadedData); echo "</pre>"; die();

        return $this->loadedData;
    }
}