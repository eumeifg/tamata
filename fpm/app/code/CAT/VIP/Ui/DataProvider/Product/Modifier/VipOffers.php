<?php

namespace CAT\VIP\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form;

class VipOffers extends AbstractModifier
{
    const SORT_ORDER = 40;

    protected $locator;

    protected $websiteRepository;

    protected $groupRepository;

    protected $storeRepository;

    protected $websitesOptionsList;

    protected $storeManager;

    protected $websitesList;

    private $dataScopeName;

    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository,
        $dataScopeName
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
        $this->dataScopeName = $dataScopeName;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        if (!$this->storeManager->isSingleStoreMode()) {
            $meta = array_replace_recursive(
                $meta,
                [
                    'viporders' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'admin__fieldset-product-customclass',
                                    'label' => __('VIP Offers'),
                                    'collapsible' => true,
                                    'componentType' => Form\Fieldset::NAME,
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $meta,
                                        'search-engine-optimization',
                                        self::SORT_ORDER
                                    ),
                                ],
                            ],
                        ],
                        'children' => $this->getPanelChildren(),
                    ],
                ]
            );
        }

        return $meta;
    }

    protected function getPanelChildren()
    {
        return [
            'viporders_products_button_set' => $this->getButtonSet()

        ];
    }

    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'CAT_VIP/js/components/container-tabname-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            '<span>You can <strong>Create/Edit</strong> vip offers here.
                             Please click on <strong>VIP Offers</strong> to proceed. 
                             For configurable products, add vip Offer on each simple product - not here!</span>
                             <div id="vip_offer_content"></div>'
                        ),
                        'template' => 'ui/form/components/complex',
                        'createTabButton' => 'ns = ${ $.ns }, index = create_viporders_products_button',
                    ],
                ],
            ],
            'children' => [

                'create_viporders_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $this->dataScopeName . '.vipOfferModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' => $this->dataScopeName . '.vipOfferModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('VIP Offers'),
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
