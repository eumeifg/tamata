<?php

namespace Magedelight\Sales\Model\AdminOrder;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\AdminOrder\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\AdminOrder\Product\Quote\Initializer;
use Psr\Log\LoggerInterface;

class Create extends \Magento\Sales\Model\AdminOrder\Create
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        LoggerInterface $logger,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Product\Quote\Initializer $quoteInitializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\AdminOrder\EmailSender $emailSender,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Updater $quoteItemUpdater,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magedelight\Catalog\Helper\Data $mdCatalogHelper,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ExtensibleDataObjectConverter $dataObjectConverter = null,
        StoreManagerInterface $storeManager = null)
    {
        parent::__construct($objectManager, $eventManager, $coreRegistry, $salesConfig, $quoteSession, $logger, $objectCopyService, $messageManager, $quoteInitializer, $customerRepository, $addressRepository, $addressFactory, $metadataFormFactory, $groupRepository, $scopeConfig, $emailSender, $stockRegistry, $quoteItemUpdater, $objectFactory, $quoteRepository, $accountManagement, $customerFactory, $customerMapper, $quoteManagement, $dataObjectHelper, $orderManagement, $quoteFactory, $data, $serializer, $dataObjectConverter, $storeManager);
        $this->mdCatalogHelper = $mdCatalogHelper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Update quantity of order quote items
     *
     * @param array $items
     * @return \Magento\Sales\Model\AdminOrder\Create
     * @throws \Exception|\Magento\Framework\Exception\LocalizedException
     */
    public function updateQuoteItems($items)
    {
        if (!is_array($items)) {
            return $this;
        }

        try {
            foreach ($items as $itemId => $info) {
                if (!empty($info['configured'])) {
                    $item = $this->getQuote()->updateItem($itemId, $this->objectFactory->create($info));
                    $info['qty'] = (double)$item->getQty();
                } else {
                    $item = $this->getQuote()->getItemById($itemId);
                    if (!$item) {
                        continue;
                    }
                    $info['qty'] = (double)$info['qty'];
                }

                /* Start Customization */

                $vendorId = $info['vendor_id'];
                $productId = $item->getProduct()->getId();

                $price = $optionId = '';

                if ($item->getProductType() == 'configurable') {
                    $optionId = $item->getOptionByCode('simple_product')->getValue();
                }

                if ($vendorId) {
                    if ($optionId) {
                        $price = $this->mdCatalogHelper->getVendorFinalPrice($vendorId, $optionId);
                    } else {
                        $price = $this->mdCatalogHelper->getVendorFinalPrice($vendorId, $productId);
                    }
                    if($price){
                        $info['custom_price'] = $price;
                    }
                }
                /* End Customization */

                $this->quoteItemUpdater->update($item, $info);

                if ($item && !empty($info['action'])) {
                    $this->moveQuoteItem($item, $info['action'], $item->getQty());
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->recollectCart();
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        $this->recollectCart();

        return $this;
    }


}