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

namespace Ktpl\SearchAutocomplete\Index\Ktpl\Kb;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;

/**
 * Class Article
 *
 * @package Ktpl\SearchAutocomplete\Index\Ktpl\Kb
 */
class Article extends AbstractIndex
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

        /** @var \Ktpl\Kb\Model\Article $article */
        foreach ($this->getCollection() as $article) {
            $items[] = [
                'name' => $article->getName(),
                'url' => $article->getUrl(),
            ];
        }

        return $items;
    }
}
