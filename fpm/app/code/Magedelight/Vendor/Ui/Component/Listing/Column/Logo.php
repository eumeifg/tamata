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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;

class Logo extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    const NAME = 'logo';
    const ALT_FIELD = 'name';

    protected $_storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magedelight\Vendor\Helper\Image $imageHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_storeManager = $storeManagerInterface;
        $this->urlBuilder = $urlBuilder;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $vendor = new \Magento\Framework\DataObject($item);

                $imageHelper = $this->imageHelper->init($vendor, $item[self::NAME]);
                $item[$fieldName . '_src'] = $imageHelper->getUrl();//$imageUrl;
                $item[$fieldName . '_alt'] = $item[self::ALT_FIELD];
                $item[$fieldName . '_orig_src'] = $imageHelper->getUrl();//$imageUrl;
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'vendor/index/edit',
                    ['vendor_id' => $vendor->getVendorId(), 'store' => $this->context->getRequestParam('store')]
                );
            }
        }
        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
