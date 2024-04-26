<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
namespace Magedelight\MobileInit\Model;

use Magedelight\MobileInit\Api\MobileInitInterface;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Helper\Data as ReviewHelper;
use Magento\SendFriend\Helper\Data as SendFriendHelper;

class MobileInit implements MobileInitInterface
{
    /**
     * Top menu data tree
     *
     * @var \Magento\Framework\Data\Tree\Node
     */
    protected $_menu;

    /**
     * Filters configuration
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected $_filters = [];

    /**
     * Filter rendered flag
     *
     * @var bool
     */
    protected $_isFiltersRendered = false;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    protected $categoryFactory;

    /**
     * MobileInit constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Quote\Api\CartManagementInterface $cartItem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param Source\Config\FrontModules $frontModules
     * @param \Magedelight\MobileInit\Api\Data\MobileInitDataInterface $mobileInitDataInterface
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param MobileInitData $mobileInitData
     * @param \Magedelight\MobileInit\Api\Data\MobileCategoryDataInterfaceFactory $mobileCategoryDataFactory
     * @param \Magedelight\MobileInit\Api\Data\MobileConfigModuleInterfaceFactory $mobileConfigModuleInterface
     * @param \Magedelight\MobileInit\Api\Data\MobileSettingDataInterface $mobileSettingDataInterface
     * @param \Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterface $mobilePriceFormatDataInterface
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Directory\Model\Currency $currency,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Quote\Api\CartManagementInterface $cartItem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magedelight\MobileInit\Model\Source\Config\FrontModules $frontModules,
        \Magedelight\MobileInit\Api\Data\MobileInitDataInterface $mobileInitDataInterface,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magedelight\MobileInit\Model\MobileInitData $mobileInitData,
        \Magedelight\MobileInit\Api\Data\MobileCategoryDataInterfaceFactory $mobileCategoryDataFactory,
        \Magedelight\MobileInit\Api\Data\MobileConfigModuleInterfaceFactory $mobileConfigModuleInterface,
        \Magedelight\MobileInit\Api\Data\MobileSettingDataInterface $mobileSettingDataInterface,
        \Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterface $mobilePriceFormatDataInterface,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->storeManager = $storeManager;
        $this->localeFormat = $localeFormat;
        $this->storeRepository = $storeRepository;
        $this->currency = $currency;
        $this->logger = $logger;
        $this->userContext = $userContext;
        $this->cartItem = $cartItem;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->frontModules = $frontModules;
        $this->mobileInitDataInterface = $mobileInitDataInterface;
        $this->currencyFactory = $currencyFactory;
        $this->mobileInitData = $mobileInitData;
        $this->mobileCategoryDataFactory = $mobileCategoryDataFactory;
        $this->mobileConfigModuleInterface = $mobileConfigModuleInterface;
        $this->mobileSettingDataInterface = $mobileSettingDataInterface;
        $this->mobilePriceFormatDataInterface = $mobilePriceFormatDataInterface;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * get available currency
     */
    protected function getAvailabelCurrency()
    {
        $currencies = $this->currency->getConfigAllowCurrencies();
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();

        foreach ($currencies as $currency) {
            $settingcurrency = [];
            $iscurrentCurrency = false;
            $currencyDetails = $this->currency->load($currency);

            $settingcurrency['currencySymbol'] = $currencyDetails->getCurrencySymbol();
            $settingcurrency['currencyCode'] = $currencyDetails->getCurrencyCode();
            if ($currentCurrencyCode == $currencyDetails->getCurrencyCode()) {
                $iscurrentCurrency = true;
            }
            $settingcurrency['isSelected'] = $iscurrentCurrency;
            $settingcurrency['label'] = $currencyDetails->getCurrencyCode() . ' - ' .
            $currencyDetails->getCurrencySymbol();
            $currencys[] = $settingcurrency;
        }
        return $currencys;
    }

    /**
     * get available currency
     */
    protected function getAvailabellanguage()
    {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            if ((int) $store->getId() == 0) {
                continue;
            }
            $response[] = ['id' => (string) $store->getId(),
                'label' => $store->getName(),
                'code' => $store->getCode(),
                //'isSelected' => $defaultStore->getId() == $store->getId() ? true : false,
            ];
        }
        return $response;
    }

    /**
     * @param $storeId
     */
    protected function getAvailabelStores($storeId)
    {
        $stores = $this->storeManager->getStores($withDefault = false);
        $currentStore = $storeId;
        $isCurrent = false;
        $locales = [];
        foreach ($stores as $store) {
            $isCurrent = false;
            $this->mobileSettingDataInterface->setId($store->getStoreId());
            $this->mobileSettingDataInterface->setLabel($store->getName());
            $this->mobileSettingDataInterface->setCode($store->getCode());
            if ($currentStore == $store->getStoreId()) {
                $isCurrent = true;
            }
            $this->mobileSettingDataInterface->setIsSelected($isCurrent);
            $locales[] = $this->mobileSettingDataInterface->getData();
        }
        $this->mobileInitDataInterface->setAvailableLanguages($locales);

        return $this;
    }

    /**
     * @param $node
     * @param null $depth
     * @param int $currentLevel
     * @return mixed
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTree($node, $depth = null, $currentLevel = 0)
    {
        $children = $this->getChildren($node, $depth, $currentLevel);
        $tree = $this->mobileCategoryDataFactory->create();
        $store = $this->storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
        $categoryIcon = $node->getSmallImage() ? $mediaUrl . $node->getSmallImage() : null;
        $mobileCategoryBanner = $node->getMobileCategoryBanner() ? $mediaUrl . $node->getMobileCategoryBanner() : null;
        $mobileCategoryImage = $node->getMobileCategoryImage() ? $mediaUrl . $node->getMobileCategoryImage() : null;
        $tree->setCategoryId($node->getId())
            ->setCategoryLabel($node->getName())
            ->setCategoryIcon($categoryIcon)
            ->setMobileCategoryBanner($mobileCategoryBanner)
            ->setMobileCategoryImage($mobileCategoryImage)
            ->setChildrenData($children);
        return $tree->getData();
    }

    /**
     * @param $node
     * @param $depth
     * @param $currentLevel
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getChildren($node, $depth, $currentLevel)
    {
        if ($node->hasChildren() && $node->getLevel() <= 3) {
            $children = [];
            $childCategory = explode(',', $node->getChildren());
            $collection = $this->categoryCollectionFactory->create();
            $collection = $this->categoryFactory->create()->getCollection()
                ->addAttributeToSelect(['name', 'small_image', 'mobile_category_banner', 'mobile_category_image'])
                ->addAttributeToFilter('is_active', '1')
//                ->addAttributeToFilter('include_in_menu', '1')
                ->addFieldToFilter([['attribute' => 'include_in_menu','eq' => '1' ],
                    ['attribute' => 'is_only_for_mobile','eq' => '1' ]])
                ->addAttributeToFilter('entity_id', ['in' => $childCategory])
                ->addAttributeToSort('position', 'asc');

            foreach ($collection as $child) {
                if ($depth !== null && $depth <= $currentLevel) {
                    break;
                }
                if ($child->getIsActive() && $child->getIncludeInMenu() || $child->getIsOnlyForMobile()) {
                    $children[] = $this->getTree($child, $depth, $currentLevel + 1);
                }
            }
            return $children;
        }
        return [];
    }

    /**
     * @param $rootCategoryId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildCategoryTree($rootCategoryId)
    {

        $collection = $this->categoryFactory->create()->getCollection()
            ->addAttributeToSelect(['name', 'small_image', 'mobile_category_banner', 'mobile_category_image'])
            ->addAttributeToFilter('parent_id', ['eq' => $rootCategoryId])
            ->addAttributeToFilter('is_active', '1')
//          ->addAttributeToFilter('include_in_menu', '1')
//          ->addAttributeToFilter([
//                             ['attribute' => 'include_in_menu','eq' => '1' ],
//                             ['attribute' => 'is_only_for_mobile','eq' => '1' ]
//                        ])
            ->addFieldToFilter([['attribute' => 'include_in_menu','eq' => '1' ],
                ['attribute' => 'is_only_for_mobile','eq' => '1' ]])
            ->addAttributeToFilter('entity_id', ['neq' => $rootCategoryId]);

        $data = [];
        foreach ($collection as $category) {
            if ($category->getIsActive() && $category->getIncludeInMenu() || $category->getIsOnlyForMobile()) {
                $data[] = $this->getTree($category, null, 0);
            }
        }

        $this->mobileInitData->addCategoryItem($data);
        return $this;
    }

    /**
     *
     */
    protected function isConfigModule()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $frontModules = $this->scopeConfig->getValue('mobileinit/general/front_modules', $storeScope);
        $frontModuleArray = explode(',', $frontModules);
        $frontModuleOptions = $this->frontModules->getModuleOptions($frontModuleArray);
        $configModuleArray = [];
        foreach ($frontModuleOptions as $key => $frontModule) {
            if ($frontModule === 0) {
                $moduleStatus = (bool) 0;
            } else {
                $moduleStatus = (bool) $this->scopeConfig->getValue($frontModule, $storeScope);
            }
            $moduleConfig = $this->mobileConfigModuleInterface->create();
            $moduleConfig->setModuleName($key)
                ->setModuleStatus($moduleStatus);
            $configModuleArray[] = $moduleConfig->getData();
        }
        $this->mobileInitDataInterface->setConfigModules($configModuleArray);
        return $this;
    }

    /**
     * @param array $priceFormat
     */
    protected function setPriceFormat(array $priceFormat)
    {
        $this->mobilePriceFormatDataInterface->setPrecision($priceFormat['precision']);
        $this->mobilePriceFormatDataInterface->setDecimalSymbol($priceFormat['decimalSymbol']);
        $priceFormatData[] = $this->mobilePriceFormatDataInterface->getData();
        $this->mobileInitDataInterface->setPriceFormat($priceFormatData);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function initApp()
    {
        $storeId = $this->request->getParam('storeId');
        if (empty($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        try {
            $this->storeManager->setCurrentStore($storeId);
            $storeData = $this->storeManager->getStore();
        } catch (\Magento\Framework\Exception\State\InitException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        }

        try {
            $catCollection = $this->categoryFactory->create()->loadByAttribute('url_key', 'deal-zone');
            $currencyCode = $storeData->getDefaultCurrencyCode();
            $currencyModel = $this->currencyFactory->create()->load($currencyCode);
            $currencySymbol = $currencyModel->getCurrencySymbol() ? $currencyModel->getCurrencySymbol() : $currencyCode;

            $priceFormat = $this->localeFormat->getPriceFormat(
                $storeData->getConfig(DirectoryHelper::XML_PATH_DEFAULT_LOCALE),
                $currencyCode
            );

            $this->mobileInitDataInterface->setDealZoneCatId($catCollection->getEntityId());
            $this->mobileInitDataInterface->setDealZoneCatTitle($catCollection->getName());
            $this->setPriceFormat($priceFormat);
            $this->mobileInitDataInterface->setCurrentLanguage(
                $storeData->getConfig(DirectoryHelper::XML_PATH_DEFAULT_LOCALE)
            );
            $this->mobileInitDataInterface->setCurrentCurrency($currencyCode);
            $this->mobileInitDataInterface->setCurrentCurrencySymbol($currencySymbol);
            $this->mobileInitDataInterface->setIsAllowedGuestCheckout(
                $storeData->getConfig(CheckoutHelper::XML_PATH_GUEST_CHECKOUT)
            );
            $this->mobileInitDataInterface->setIsAllowedGuestReview(
                $storeData->getConfig(ReviewHelper::XML_REVIEW_GUETS_ALLOW)
            );
            $this->mobileInitDataInterface->setIsAllowedGuestReferral(
                $storeData->getConfig(SendFriendHelper::XML_PATH_ALLOW_FOR_GUEST)
            );

            $this->getCustomerCartData();
            $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
            $this->buildCategoryTree($rootCategoryId);
            $this->isConfigModule();
            $this->getAvailabelStores($storeId);
            $this->mobileInitDataInterface->setWebsiteId($storeData->getWebsiteId());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return;
        }

        return $this->mobileInitDataInterface;
    }

    /**
     *
     */
    protected function getCustomerCartData()
    {
        $customerId = $this->userContext->getUserId();
        $cartCount = 0;
        $cart_items_qty = 0;
        $loggedIn = false;
        if ($customerId && $customerId != 0) {
            try {
                $data = $this->cartItem->getCartForCustomer($customerId);
                if (!empty($data) && isset($data)) {
                    $cartCount = $data->getItemsCount();
                    $cart_items_qty = round($data->getItemsQty(), 0);
                }
            } catch (\Exception $e) {
                $cartCount = 0;
                $cart_items_qty = 0;
            }
            $loggedIn = true;
        }
        $this->mobileInitDataInterface->setCartItems($cartCount);
        $this->mobileInitDataInterface->setCartItemsQuantity($cart_items_qty);
        return $this;
    }
}
