<?php

namespace Ktpl\Ajaxscroll\Plugin;

class PagerPlugin
{

    private $urlBuilder;

    public function __construct(\Amasty\ShopbyBase\Model\UrlBuilder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param array $params
     * @return mixed
     */
    public function aroundGetPagerUrl($subject, callable $proceed, $params = [])
    {
        $params['is_scroll'] = true;
        if ($subject->getNameInLayout() != 'product_list_toolbar_pager') {
            return $proceed($params);
        }
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['is_scroll'] = true;
        $urlParams['_query'] = $params;
        return $this->urlBuilder->getUrl('*/*/*', $urlParams);
    }
}
