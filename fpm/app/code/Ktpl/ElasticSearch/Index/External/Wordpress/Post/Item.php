<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Index\External\Wordpress\Post;

/**
 * Class Item
 *
 * @package Ktpl\ElasticSearch\Index\External\Wordpress\Post
 */
class Item extends \Magento\Framework\DataObject
{
    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        $url = $this->getIndex()->getModel()->getProperty('url_template');

        $data = $this->getData();

        $data['year'] = date('Y', strtotime($data['post_date']));
        $data['monthnum'] = date('m', strtotime($data['post_date']));
        $data['day'] = date('d', strtotime($data['post_date']));

        foreach ($data as $key => $value) {
            $key = strtolower($key);
            if (is_scalar($value)) {
                $url = str_replace('{' . $key . '}', $value, $url);
            }
        }

        return $url;
    }

    /**
     * Get teaser
     *
     * @return string
     */
    public function getTeaser()
    {
        $contents = explode('<!--more-->', $this->getData('post_content'));
        $teaser = strip_tags($contents[0]);
        return $teaser;
    }
}
