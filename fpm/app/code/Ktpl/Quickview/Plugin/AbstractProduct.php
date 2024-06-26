<?php

namespace Ktpl\Quickview\Plugin;

class AbstractProduct
{
    /**
     * @var  \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * ResultPage constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Block\Product\AbstractProduct $subject
     * @param $result
     * @return bool
     */
    public function afterIsRedirectToCartEnabled(
        \Magento\Catalog\Block\Product\AbstractProduct $subject,
        $result
    ) {
        $requestUri = $this->request->getRequestUri();
        if (strpos($requestUri, 'ktpl_quickview/catalog_product/view') !== false) {
            $result = false;
        }

        return $result;
    }
}
