<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Ui\Component\Listing\Column;


class KtplpushnotificationsActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const URL_PATH_DETAILS = 'ktpl_pushnotification/ktplpushnotifications/details';
    const URL_PATH_DELETE = 'ktpl_pushnotification/ktplpushnotifications/delete';
    protected $urlBuilder;
    const URL_PATH_EDIT = 'ktpl_pushnotification/ktplpushnotifications/edit';

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $fieldName = 'image_url';
                    if($item[$fieldName] != '') {

                        $url = $item[$fieldName];

                        $item[$fieldName . '_src']  = $url;
                        $item[$fieldName . '_alt']  = 'image';

                        $item[$fieldName . '_orig_src'] = $url;
                    }

                    $item[$this->getData('name')] = [
                        // 'edit' => [
                        //     'href' => $this->urlBuilder->getUrl(
                        //         static::URL_PATH_EDIT,
                        //         [
                        //             'id' => $item['id']
                        //         ]
                        //     ),
                        //     'label' => __('Edit')
                        // ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $item['id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.title }"'),
                                'message' => __('Are you sure you wan\'t to delete a "${ $.$data.title }" record?')
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}

