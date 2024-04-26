<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model\View\Result;

use Magedelight\Backend\App\AbstractAction;
use Magedelight\Vendor\Model\Session;
use Magedelight\Backend\Model\UrlInterface;
use Magento\Framework\App;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;

/**
 * Description of Redirect
 *
 * @author Rocket Bazaar Core Team
 */
class Redirect extends \Magento\Framework\Controller\Result\Redirect
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * Constructor
     *
     * @param App\Response\RedirectInterface $redirect
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param Session $session
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        App\Response\RedirectInterface $redirect,
        \Magedelight\Backend\Model\UrlInterface $urlBuilder,
        Session $session,
        ActionFlag $actionFlag
    ) {
        $this->session = $session;
        $this->actionFlag = $actionFlag;
        parent::__construct($redirect, $urlBuilder);
    }

    /**
     * Set referer url or dashboard if referer does not exist
     *
     * @return $this
     */
    public function setRefererOrBaseUrl()
    {
        $this->url = $this->redirect->getRedirectUrl($this->urlBuilder->getUrl($this->urlBuilder->getStartupPageUrl()));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function render(HttpResponseInterface $response)
    {
        $this->session->setIsUrlNotice($this->actionFlag->get('', AbstractAction::FLAG_IS_URLS_CHECKED));
        return parent::render($response);
    }
    //put your code here
}
