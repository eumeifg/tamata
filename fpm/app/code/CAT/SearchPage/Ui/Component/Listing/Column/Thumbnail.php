<?php


namespace CAT\SearchPage\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'banner';
    const ALT_FIELD = 'name';
    protected $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array                 $components = [],
        array                 $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
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
            $path = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item['banner']) {
                    $item[$fieldName . '_src'] = $path . 'cat/searchPage/' . $item['banner'];
                    $item[$fieldName . '_alt'] = $item['banner'];
                    $item[$fieldName . '_orig_src'] = $path . 'cat/searchPage/' . $item['banner'];
                } else {
                    $item[$fieldName . '_src'] = $path . 'cat/searchPage/placeholder/placeholder.jpg';
                    $item[$fieldName . '_alt'] = 'Place Holder';
                    $item[$fieldName . '_orig_src'] = $path . 'cat/searchPage/placeholder/placeholder.jpg';
                }
            }
        }

        return $dataSource;
    }
}
