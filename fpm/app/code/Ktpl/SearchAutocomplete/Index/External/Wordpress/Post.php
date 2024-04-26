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

namespace Ktpl\SearchAutocomplete\Index\External\Wordpress;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;

/**
 * Class Post
 *
 * @package Ktpl\SearchAutocomplete\Index\External\Wordpress
 */
class Post extends AbstractIndex
{
    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        /** @var \Ktpl\ElasticSearch\Model\Index\External\Wordpress\Post\Item $post */
        foreach ($this->getCollection() as $post) {
            $items[] = [
                'name' => $post->getData('post_title'),
                'url' => $post->getUrl(),
            ];
        }

        return $items;
    }
}
