<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Model\Rule;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magedelight\Abandonedcart\Model\ResourceModel\Rule\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var ResourceModel\Data\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var \Magedelight\Abandonedcart\Model\EmailscheduleFactory
     */
    public $emailScheduleFactory;
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Magedelight\Abandonedcart\Model\EmailscheduleFactory $emailScheduleFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $pageCollectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magedelight\Abandonedcart\Model\EmailscheduleFactory $emailScheduleFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $pageCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->meta           = $this->prepareMeta($this->meta);
        $this->emailScheduleFactory = $emailScheduleFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    
    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        $ruleId = '';
        foreach ($items as $page) {
            $this->loadedData[$page->getId()] = $page->getData();
            $ruleId = $page->getId();
        }
        $data = $this->dataPersistor->get('module_messages');
        if (!empty($data)) {
            $page = $this->collection->getNewEmptyItem();
            $page->setData($data);
            $this->loadedData[$page->getId()] = $page->getData();
            $this->dataPersistor->clear('module_messages');
        }
        if (!empty($ruleId)) {
            /** @var $emailScheduleModel \\Magedelight\Abandonedcart\Model\EmailscheduleFactory */
            $emailScheduleModel = $this->emailScheduleFactory->create();
            $emailScheduleModel =  $emailScheduleModel->getCollection()
            ->addFieldToFilter('abandoned_cart_rule_id', ['eq'=>$ruleId]);

            $this->loadedData[$ruleId]['scheduled_email'] = $emailScheduleModel->getData();
        }
        
        return $this->loadedData;
    }
}
