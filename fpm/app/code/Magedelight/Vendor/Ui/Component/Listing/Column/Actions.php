<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{

    /**
     *
     * @var string
     */
    private $editUrl;

    /** Url path */
    const VENDOR_URL_PATH_EDIT = 'vendor/index/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = [],
        $editUrl = self::VENDOR_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                if (array_key_exists('websites', $item)) {
                    $websiteIds = explode(",", $item['websites']);
                    foreach ($websiteIds as $websiteId) {
                        $storeId = (array_key_exists('store_id', $item)) ?
                            $item['store_id'] : $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
                        $website = $this->storeManager->getWebsite($websiteId);
                        $item[$this->getData('name')]['edit' . $websiteId] = [
                            'href' => $this->urlBuilder->getUrl(
                                $this->editUrl,
                                ['vendor_id' => $item['vendor_id'], 'store' => $storeId,'website_id' => $websiteId]
                            ),
                            'label' => __('Edit - ' . $website->getName()),
                            'hidden' => false,
                        ];
                    }
                } else {
                    $item[$this->getData('name')]['edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                $this->editUrl,
                                ['vendor_id' => $item['vendor_id'], 'store' => $storeId]
                            ),
                            'label' => __('Edit'),
                            'hidden' => false,
                        ];
                }
            }
        }
        return $dataSource;
    }
}
