<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class HistoryActions extends Column
{
    /** Url path */
    const BLOG_URL_PATH_RESEND = 'abandonedcart/history/resend';
    
    /** @var UrlInterface */
    protected $urlBuilder;
    protected $helperBackend;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Backend\Helper\Data $helperBackend,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helperBackend = $helperBackend;
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
                $name = $this->getData('name');

                if ($item['status'] == 1) {
                    if (isset($item['history_id'])) {
                        $item[$name]['resend'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::BLOG_URL_PATH_RESEND,
                                ['id' => $item['history_id']]
                            ),
                            'label' => __('Resend'),
                            'confirm' => [
                                'title' => __('Resend'),
                                'message' => __('Are you sure you wan\'t to resend email to this customer?')
                            ]
                        ];
                    }
                } else {
                    $url = $this->urlBuilder->getUrl('abandonedcart/history/index');
                    $item[$name]['blankaction'] = [
                        'href' => $this->urlBuilder->getUrl($url),
                        'label' => __('----')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
