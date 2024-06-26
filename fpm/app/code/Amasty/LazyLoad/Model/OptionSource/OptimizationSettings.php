<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_LazyLoad
 */

declare(strict_types=1);

namespace Amasty\LazyLoad\Model\OptionSource;

use Amasty\PageSpeedTools\Model\OptionSource\ToOptionArrayTrait;
use Magento\Framework\Data\OptionSourceInterface;

class OptimizationSettings implements OptionSourceInterface
{
    const SIMPLE = 0;
    const ADVANCED = 1;

    use ToOptionArrayTrait;

    public function toArray(): array
    {
        return [
            self::SIMPLE => __('Simple'),
            self::ADVANCED => __('Advanced'),
        ];
    }
}
