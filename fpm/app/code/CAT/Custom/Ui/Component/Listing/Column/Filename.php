<?php
namespace CAT\Custom\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Filename extends Column
{
    protected $storeManagaer;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManagaer = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $mediaUrl = $this->storeManagaer->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!array_key_exists('file_name', $item) || $item['file_name'] == '') {
                    continue;
                }

                $filename = array_slice(explode('/', $item['file_name']), -1, 1)[0];
                $uploadedFile = $mediaUrl . 'cat/'.$item['entity_type'].'/' . $filename;

                $filenameHtml = __('<a href="%1">%2</a>', $uploadedFile, $item['file_name']);
                $item['file_name'] = $filenameHtml;
            }
        }
        return $dataSource;
    }
}
