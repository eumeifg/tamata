<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Cron;

use Magedelight\ConfigurableProduct\Model\ConfigurableProductResolver;

class RemoveProductsWithNoChild
{
    /**
     * @var ConfigurableProductResolver
     */
    protected $configurableProductResolver;

    /**
     * RemoveProductsWithNoChild constructor.
     * @param ConfigurableProductResolver $configurableProductResolver
     */
    public function __construct(
        ConfigurableProductResolver $configurableProductResolver
    ) {
        $this->configurableProductResolver = $configurableProductResolver;
    }

    /**
     *
     * @return void
     */
    public function execute()
    {
        $this->configurableProductResolver->deleteParentProductsWithNoChild();
    }
}
