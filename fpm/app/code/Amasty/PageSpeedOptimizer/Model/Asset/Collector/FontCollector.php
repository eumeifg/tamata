<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


declare(strict_types=1);

namespace Amasty\PageSpeedOptimizer\Model\Asset\Collector;

class FontCollector extends AbstractAssetCollector
{
    const REGEX = '/<link[^>]*href\s*=\s*["|\'](?<asset_url>.*?(eot|fft|otf|woff2|woff))["\']+[^>]*>/i';

    public function getAssetContentType(): string
    {
        return 'font';
    }
}
