<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form;

class VendorOffers extends AbstractModifier
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
                    'tabname' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'admin__fieldset-product-customclass',
                                    'label' => __('Vendor Offers'),
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
            'tabname_products_button_set' => $this->getButtonSet()

        ];
    }

    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magedelight_Catalog/js/components/container-tabname-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            '<span>You can <strong>Create/Edit</strong> vendor offers here.
                             Please click on <strong>Vendor Offers</strong> to proceed. 
                             For configurable products, add Vendor Offer on each simple product - not here!</span>
                             <div id="vendor_offer_content"></div>'
                        ),
                        'template' => 'ui/form/components/complex',
                        'createTabButton' => 'ns = ${ $.ns }, index = create_tabname_products_button',
                    ],
                ],
            ],
            'children' => [

                'create_tabname_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $this->dataScopeName . '.vendorOfferModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' => $this->dataScopeName . '.vendorOfferModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Vendor Offers'),
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
