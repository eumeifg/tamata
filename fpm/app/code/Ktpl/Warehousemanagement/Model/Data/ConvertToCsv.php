<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Warehousemanagement\Model\Data;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var Filter
     */
    protected $filter;

    private $statusCollection;

    protected $userFactory;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        $pageSize = 200,
        \Ktpl\Warehousemanagement\Helper\Data $warehouseHelper,
        \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        $this->warehouseHelper = $warehouseHelper;
        $this->statusCollection = $statusCollection;
        $this->userFactory = $userFactory;
        parent::__construct(
            $filesystem,
            $filter,
            $metadataProvider,
            $pageSize
        );
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        if ($component->getName() != "ktpl_warehousemanagement_producttrackreport") {
            return parent::getCsvFile();
        }

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {

                /*...To set custom status while export csv for warehouse track report Start...*/
                if ($item->getData('product_location') == 0)
                {
                    $productLocationStatus = $this->warehouseHelper->getDeliveryVendortoWarehouseStatus();
                }
                else {
                    $productLocationStatus = $this->warehouseHelper->getDeliveryWarehousetoCustomer();
                }

                if ($item->getData('order_event') == 2)
                {
                    $productEventStatus = $this->warehouseHelper->getReturnsVendortoWarehouse();
                }
                else {
                    $productEventStatus = $this->warehouseHelper->getDeliveryWarehousetoCustomer();
                }

                foreach ($this->statusCollection->toOptionArray() as $status) {
                    if($status['value'] == $productLocationStatus) {
                        $this->status[$status['value']] = $status['label'];
                    } elseif ($status['value'] == $productEventStatus) {
                        $this->status[$status['value']] = $status['label'];
                    }
                }
                $productLocationStatus = $this->status[$productLocationStatus];
                $productEventStatus = $this->status[$productEventStatus];
                $user = $this->userFactory->create()->load($item->getData('user_id'));
                /*...Set data to raw in CSV...*/
                $item->setproductLocation($productLocationStatus);
                $item->setorderEvent($productEventStatus);
                $item->setuserId($user->getName());
                /*...To set custom status while export csv for warehouse track report End...*/

                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
