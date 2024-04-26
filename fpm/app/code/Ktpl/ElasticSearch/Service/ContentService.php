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

namespace Ktpl\ElasticSearch\Service;

use Magento\Cms\Model\Template\FilterProvider as CmsFilterProvider;
use Magento\Email\Model\TemplateFactory as EmailTemplateFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\App\Emulation as AppEmulation;

/**
 * Class ContentService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class ContentService
{
    /**
     * @var AppEmulation
     */
    private $emulation;

    /**
     * @var CmsFilterProvider
     */
    private $filterProvider;

    /**
     * @var EmailTemplateFactory
     */
    private $templateFactory;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * ContentService constructor.
     *
     * @param AppEmulation $emulation
     * @param CmsFilterProvider $filterProvider
     * @param EmailTemplateFactory $templateFactory
     * @param AppState $appState
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        AppEmulation $emulation,
        CmsFilterProvider $filterProvider,
        EmailTemplateFactory $templateFactory,
        AppState $appState,
        ModuleManager $moduleManager
    )
    {
        $this->emulation = $emulation;
        $this->filterProvider = $filterProvider;
        $this->templateFactory = $templateFactory;
        $this->appState = $appState;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Process html content
     *
     * @param $storeId
     * @param $html
     * @return mixed|string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function processHtmlContent($storeId, $html)
    {
        $this->emulation->startEnvironmentEmulation($storeId);

        $template = $this->templateFactory->create();
        $template->emulateDesign($storeId);

        $template->setTemplateText($html)
            ->setIsPlain(false);
        $template->setTemplateFilter($this->filterProvider->getPageFilter());
        $html = $template->getProcessedTemplate([]);

        if ($this->moduleManager->isEnabled('Gene_BlueFoot')) {
            $html = $this->appState->emulateAreaCode(
                'frontend',
                [$this, 'processBlueFoot'],
                [$html]
            );
        }

        $this->emulation->stopEnvironmentEmulation();

        return $html;
    }

    /**
     * Process blue foot
     *
     * @param $html
     * @return mixed
     */
    public function processBlueFoot($html)
    {
        $ob = ObjectManager::getInstance();
        $stageRender = $ob->get('Gene\BlueFoot\Model\Stage\Render');
        $html = $stageRender->render($html);

        return $html;
    }
}