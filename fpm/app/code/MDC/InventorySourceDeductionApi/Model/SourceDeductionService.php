<?php 

namespace MDC\InventorySourceDeductionApi\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;
use Magento\InventorySourceDeductionApi\Model\GetSourceItemBySourceCodeAndSku;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;

class SourceDeductionService extends \Magento\InventorySourceDeductionApi\Model\SourceDeductionService
{
	/**
     * Constant for zero stock quantity value.
     */
    private const ZERO_STOCK_QUANTITY = 0.0;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var GetSourceItemBySourceCodeAndSku
     */
    private $getSourceItemBySourceCodeAndSku;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetStockBySalesChannelInterface
     */
    private $getStockBySalesChannel;

	public function __construct(
		SourceItemsSaveInterface $sourceItemsSave,
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockBySalesChannelInterface $getStockBySalesChannel
	)	
    {
    	$this->sourceItemsSave = $sourceItemsSave;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockBySalesChannel = $getStockBySalesChannel;

		parent::__construct($sourceItemsSave,$getSourceItemBySourceCodeAndSku,$getStockItemConfiguration,$getStockBySalesChannel);
		
	}

	public function execute(SourceDeductionRequestInterface $sourceDeductionRequest): void
    {
        $sourceItems = [];
        $salesChannel = $sourceDeductionRequest->getSalesChannel();
        $sourceCode = $sourceDeductionRequest->getSourceCode();

        $stockId = $this->getStockBySalesChannel->execute($salesChannel)->getStockId();
        foreach ($sourceDeductionRequest->getItems() as $item) {
            $itemSku = $item->getSku();
            $qty = $item->getQty();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute(
                $itemSku,
                $stockId
            );

            if (!$stockItemConfiguration->isManageStock()) {
                //We don't need to Manage Stock
                continue;
            }

            $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $itemSku);

            if (($sourceItem->getQuantity() - $qty) >= 0) {
                $sourceItem->setQuantity($sourceItem->getQuantity() - $qty);
                $stockStatus = $this->getSourceStockStatus(
                    $stockItemConfiguration,
                    $sourceItem
                );
                $sourceItem->setStatus($stockStatus);
                $sourceItems[] = $sourceItem;
            } else {

                $sourceItems[] = $sourceItem;
            }
        }

        if (!empty($sourceItems)) {
            $this->sourceItemsSave->execute($sourceItems);
        }
    }

    private function getSourceStockStatus(
        StockItemConfigurationInterface $stockItemConfiguration,
        SourceItemInterface $sourceItem
    ): int {
        $sourceItemQty = $sourceItem->getQuantity() ?: self::ZERO_STOCK_QUANTITY;
        return $sourceItemQty === $stockItemConfiguration->getMinQty() && !$stockItemConfiguration->getBackorders()
            ? SourceItemInterface::STATUS_OUT_OF_STOCK
            : SourceItemInterface::STATUS_IN_STOCK;
    }


    /**
     * @param integer $productId
     * @param string $productType
     */
    public function updateStockStatusForProduct($productId, $productType = Type::DEFAULT_TYPE)
    {
        $stockItem = $this->_stockRegistry->getStockItem($productId, 1);
        $qty = $this->getTotalProductQty($productId);

        if ($productType && $productType == Type::DEFAULT_TYPE) {
            if ($qty > 0) {
                $isInStock = true;
            } else {
                $isInStock = false;
            }
        } else {
            $isInStock = true;
        }

        $stockItemData = [
            'qty' => $qty,
            'is_in_stock' => $isInStock
        ];

        $this->dataObjectHelper->populateWithArray(
            $stockItem,
            $stockItemData,
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class
        );
        $this->stockItemRepository->save($stockItem);
        if ($stockStatus = $this->stockRegistryProvider->getStockStatus(
            $stockItem->getProductId(),
            $stockItem->getWebsiteId()
        )
        ) {
            $this->stockStatusRepository
                ->save($stockStatus)->setStockStatus($isInStock);
        }
    }

    public function updateMultipleProductStock($event, $action = null, $productIds = [])
    {
        if (!empty($event->getVendorProducts())) {
            foreach ($event->getVendorProducts() as $data) {
                $productId = $data['marketplace_product_id'];
                $this->updateStockStatusForProduct($productId);
            }
        } elseif (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $this->updateStockStatusForProduct($productId);
            }
        }

        return $this;
    }
}
?>