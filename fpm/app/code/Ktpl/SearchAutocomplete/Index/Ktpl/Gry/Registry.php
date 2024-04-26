<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Index\Ktpl\Gry;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;

/**
 * Class Registry
 *
 * @package Ktpl\SearchAutocomplete\Index\Ktpl\Gry
 */
class Registry extends AbstractIndex
{
    /**
     * Get size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->collection->getSize();
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        /** @var \Ktpl\Giftr\Model\Registry $registry */
        foreach ($this->getCollection() as $registry) {
            $items[] = [
                'title' => $registry->getName(),
                'url' => $registry->getViewUrl(),
                'name' => __('Registrant: %1', $registry->getRegistrantAndCoName()),
            ];
        }

        return $items;
    }
}
