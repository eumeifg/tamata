<?php

namespace CAT\SearchPage\Api;

use CAT\SearchPage\Api\Data\SearchPagesBuilderDataInterface;

interface SearchPagesBuilderInterface
{
    /**
     * @api
     * @return SearchPagesBuilderDataInterface
     */
    public function searchPage(): SearchPagesBuilderDataInterface;

}
