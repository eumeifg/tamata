<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


declare(strict_types=1);

namespace Amasty\Xsearch\Plugin\ElasticSearch\Model\Search\GetRequestQuery;

use Amasty\Xsearch\Model\Config;

class SortingProviderPlugin
{
    const FIELD = 'stock_status';
    const DIRECTION = 'asc';

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     */
    public function afterGetRequestedSorting($subject, array $result): array
    {
        if ($this->config->isShowOutOfStockLast()) {
            array_unshift($result, ['field' => self::FIELD, 'direction' => self::DIRECTION]);
        }

        return $result;
    }

    /**
     * @param $subject
     * @param array $result
     * @return array
     */
    public function afterGetSort($subject, array $result): array
    {
        if ($this->config->isShowOutOfStockLast()) {
            array_unshift($result, [self::FIELD => ['order' => self::DIRECTION]]);
        }

        return $result;
    }
}
