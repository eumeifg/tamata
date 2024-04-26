<?php
/**
 * Copyright © Magedelight, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\Pushnotification\Model;
use Magento\Framework\Event\ManagerInterface as EventManager;

class KtplRecentViewProductSave implements \Ktpl\Pushnotification\Api\KtplRecentViewProductInterface
{

    /**
     * @var EventManager
     */
    private $eventManager;

    protected $dataObjectFactory;

    public function __construct(
        \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterfaceFactory $ktplRecentViewProductInterfaceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ktpl\Pushnotification\Model\KtplRecentViewProductRepository $ktplRecentViewProductRepository,
        EventManager $eventManager
    ) {
        $this->ktplRecentViewProductInterfaceFactory = $ktplRecentViewProductInterfaceFactory;
        $this->storeManager = $storeManager;
        $this->ktplRecentViewProductRepository = $ktplRecentViewProductRepository;
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     * @param int $productId The Product ID.
     * @param string $deviceTokenId The Device  ID.
     * @return string.
     * @throws \Magento\Framework\Exception\LocalizedException.
     */
    public function recentlyViewdProduct($productId, $deviceTokenId)
    {
            $model =  $this->ktplRecentViewProductInterfaceFactory->create();
            $model->setDeviceTokenId($deviceTokenId);
            $model->setProductId($productId);

            $storeId = $this->storeManager->getStore()->getId();
            if (!$storeId) {
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }
      
            $model->setStoreId($storeId);
            $this->ktplRecentViewProductRepository->save($model);        
    }
}
