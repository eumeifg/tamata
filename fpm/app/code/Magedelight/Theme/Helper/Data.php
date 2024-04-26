<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LENGTH = 35;
    const DEFAULT_PRODUCT_NAME_LENGTH = 30;
    
    const XML_PATH_DEFAULT_IS_RTL = 'general/locale/is_rtl';

    protected $isCustomerLoggedIn = '';
    
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;
    
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param FilterManager $filter
     * @param Session $customer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->isCustomerLoggedIn = $httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $this->_customerSession = $customerSession;
        $this->filter = $filter;
        parent::__construct($context);
    }
    
    /**
     *
     * @return string
     */
    public function getAccountLoginUrl()
    {
        return $this->_getUrl('customer/account/login');
    }
    
    /**
     *
     * @return string
     */
    public function getAccountCreateUrl()
    {
        return $this->_getUrl('customer/account/create/');
    }
    
    /**
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_getUrl('customer/account');
    }
    
    /**
     *
     * @return string
     */
    public function getCustomerOrderUrl()
    {
        return $this->_getUrl('sales/order/history');
    }
    
    /**
     *
     * @return string
     */
    public function getWhislistUrl()
    {
        return $this->_getUrl('wishlist');
    }
    
    /**
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_getUrl('customer/account/logout');
    }
    
    /**
     *
     * @return string
     */
    public function getWelcomeName()
    {
        $name = $this->filter->truncate($this->_customerSession->getCustomer()->getFirstname(), ['length' => '10']);
        return $name;
    }
    
    /**
     *
     * @param string $name
     * @param int $length
     * @return string
     */
    public function getFormatedName($name, $length = self::LENGTH)
    {
        return $this->filter->truncate($name, ['length' => $length]);
    }
    
    /**
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getcustomerSession()
    {
        return $this->isCustomerLoggedIn;
    }
    
    /**
     *
     * @param string $config_path
     * @return string
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     *
     * @return bool
     */
    public function getRTLFlag()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_IS_RTL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve customer login status
     *
     * @return bool
     */
    public function isCustomerLogIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
    
    /**
     *
     * @param string $moduleName
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->isModuleOutputEnabled($moduleName);
    }
    
    /**
     *
     * @return boolean
     */
    public function isMultiShippingEnabled()
    {
        return ($this->isModuleOutputEnabled('RB_Multipleshipping') &&
            $this->getConfigValue('carriers/rbmultipleshipping/active'));
    }
    
    /**
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * @param string $text
     * @param integer $length
     * @return string
     */
    public function truncate($text, $length)
    {
        $length = abs((int)$length);
        if (strlen($text) > $length) {
            $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
        }
        return($text);
    }
    
    /**
     *
     * @param string $text
     * @param integer $length
     * @param string $ending
     * @param boolean $exact
     * @param boolean $considerHtml
     * @return string
     */
    public function truncateWithHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {
        if ($considerHtml) {
          // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
          // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = [];
            $truncate = '';
            foreach ($lines as $line_matchings) {
              // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                  // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                      // do nothing
                      // if tag is a closing tag
                    } elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                      // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                      // if tag is an opening tag
                    } elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                      // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                  // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
              // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length> $length) {
                  // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                  // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                      // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                              // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                  // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
              // if the maximum length is reached, get off the loop
                if ($total_length>= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
    // if the words shouldn't be cut in the middle...
        if (!$exact) {
          // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
              // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
    // add the defined ending to the text
        $truncate .= $ending;
        if ($considerHtml) {
          // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        return $truncate;
    }
}
