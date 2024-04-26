<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Model;

class Observer extends \Magento\ProductAlert\Model\Observer
{
    protected $_customerSession;

    protected $_logger;

    protected $_registry;

    protected $_helper;

    protected $_objectManager;

    protected $_colFactorys = [];

    protected $_configurableType;

    protected $_state;

    protected $customer;

    protected $dataCustomer;

    protected $stockItem;

    protected $modelProduct;

    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory $priceColFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockColFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\ProductAlert\Model\EmailFactory $emailFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magedelight\ProductAlert\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Data\Customer $dataCustomer,
        \Magento\CatalogInventory\Model\Stock\Item $stockItem,
        \Magento\Catalog\Model\Product $modelProduct
    ) {
        parent::__construct(
            $catalogData,
            $scopeConfig,
            $storeManager,
            $priceColFactory,
            $customerRepository,
            $productRepository,
            $dateFactory,
            $stockColFactory,
            $transportBuilder,
            $emailFactory,
            $inlineTranslation
        );
        $this->_customerSession = $customerSession;
        $this->_logger = $logger;
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_colFactorys['price'] = $priceColFactory;
        $this->_colFactorys['stock'] = $stockColFactory;
        $this->_configurableType = $configurableType;
        $this->customer = $customer;
        $this->dataCustomer = $dataCustomer;
        $this->stockItem = $stockItem;
        $this->modelProduct =$modelProduct;
    }


    protected function _processStock(\Magento\ProductAlert\Model\Email $email)
    {
        $this->_sendNotifications('stock', $email);
    }

    protected function _processPrice(\Magento\ProductAlert\Model\Email $email)
    {
        $this->_sendNotifications('price', $email);
    }

    protected function _sendNotifications($type, $email)
    {
        $email->setType($type);
        foreach ($this->_getWebsites() as $website) {
            /* @var $website \Magento\Store\Model\Website */

            if (!$website->getDefaultGroup()
                || !$website->getDefaultGroup()->getDefaultStore()
            ) {
                continue;
            }

            if (!$this->_scopeConfig->getValue(
                'catalog/productalert/allow_' . $type,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }
            try {
                $collection = $this->_colFactorys[$type]->create()
                    ->addWebsiteFilter(
                        $website->getId()
                    )
                    ->addFieldToFilter('status', 0)
                    ->setCustomerOrder();
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                return $this;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($collection as $alert) {
                $this->_registry->unregister('md_data');
                $storeId = $alert->getStoreId()? $alert->getStoreId():  $website->getDefaultStore()->getId();
                try {
                    $email->clean();
                    $isGuest = (0 == $alert->getCustomerId()) ? 1 : 0;
                    if ($isGuest) {
                        $this->_registry->register(
                            'md_data',
                            [
                                'guest' => 1,
                                'email' => $alert->getEmail()
                            ]
                        );

                        $customer = $this->customer;
                        $customer->setWebsiteId(
                            $this->_storeManager->getWebsite()->getId()
                        );
                        $customer->loadByEmail($alert->getEmail());

                        if (!$customer->getId()) {
                            $customer = $this->dataCustomer->setWebsiteId(
                                $this->_storeManager->getWebsite()->getId()
                            )->setEmail(
                                $alert->getEmail()
                            )->setLastname(
                                $this->_scopeConfig->getValue(
                                    'rbnotification/general/customer_name'
                                )
                            )->setGroupId(
                                0
                            )->setId(
                                0
                            );
                        } else {
                            $customer = $this->customerRepository->getById(
                                $customer->getId()
                            );
                        }
                    } else {
                        $customer = $this->customerRepository->getById(
                            $alert->getCustomerId()
                        );
                    }

                    if (!$customer) {
                        continue;
                    }
                    $customer->setStoreId($storeId);
                    $email->setCustomerData($customer);

                    $product = $this->productRepository->getById(
                        $alert->getProductId(),
                        false,
                        $storeId
                    );

                    if (!$product) {
                        continue;
                    }

                    $product->setCustomerGroupId($customer->getGroupId());

                    /*stock functionality start*/
                    if ('stock' == $type) {
                        $minQuantity = $this->_scopeConfig->getValue(
                            'rbnotification/general/min_qty'
                        );
                        if ($minQuantity < 1) {
                            $minQuantity = 1;
                        }

                        $isInStock = false;
                        if ($product->canConfigure() && $product->isInStock()) {
                            $allProducts = $this->_configurableType->getUsedProducts($product);
                            foreach ($allProducts as $simpleProduct) {
                                $stockItem = $this->stockItem->load($simpleProduct->getId(), 'product_id');

                                $quantity = $stockItem->getData('qty');
                                $isInStock =
                                    (
                                        $simpleProduct->isSalable()
                                        || $simpleProduct->isSaleable()
                                    )
                                    && $quantity >= $minQuantity;
                                if ($isInStock) {
                                    break;
                                }
                            }
                        } else {
                            $stockItem = $this->stockItem->load($product->getId(), 'product_id');

                            $quantity = $stockItem->getData('qty');
                            $isInStock =
                                ($product->isSalable())
                                && ($quantity >= $minQuantity);
                        }

                        if ($isInStock) {
                            if ($alert->getParentId()
                                && $alert->getParentId() != $alert->getProductId()
                                && !$product->canConfigure()
                            ) {
                                $productParent = $this->modelProduct->setStoreId(
                                    $website->getDefaultStore()->getId()
                                )->load($alert->getParentId());

                                $email->addStockProduct($productParent);
                            } else {
                                $email->addStockProduct($product);
                            }

                            $alert->setSendDate(
                                $this->_dateFactory->create()->gmtDate()
                            );
                            $alert->setSendCount($alert->getSendCount() + 1);
                            $alert->setStatus(1);
                            $alert->save();
                        }
                        /*stock functionality end*/
                    } else {
                        /*price functionality start*/
                        if ($alert->getPrice() > $product->getFinalPrice()) {
                            $productPrice = $product->getFinalPrice();
                            $product->setFinalPrice(
                                $this->_catalogData->getTaxPrice(
                                    $product,
                                    $productPrice
                                )
                            );
                            $product->setPrice(
                                $this->_catalogData->getTaxPrice(
                                    $product,
                                    $product->getPrice()
                                )
                            );
                            $email->addPriceProduct($product);

                            $alert->setPrice($productPrice);
                            $alert->setLastSendDate(
                                $this->_dateFactory->create()->gmtDate()
                            );
                            $alert->setSendCount($alert->getSendCount() + 1);
                            $alert->setStatus(1);
                            $alert->save();
                        }
                        /*price functionality end*/
                    }
                    $email->send();
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }

        return $this;
    }
}
