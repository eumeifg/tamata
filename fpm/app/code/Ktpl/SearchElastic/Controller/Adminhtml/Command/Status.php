<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Controller\Adminhtml\Command;

use Ktpl\SearchElastic\Controller\Adminhtml\Command;

/**
 * Class Status
 *
 * @package Ktpl\SearchElastic\Controller\Adminhtml\Command
 */
class Status extends Command
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $success = false;
        $message = '';
        $note = '';

        if (class_exists('Elasticsearch\ClientBuilder')) {
            try {
                if ($this->engine->status($note)) {
                    $message = __('Elasticsearch is running.');
                    $success = true;
                } else {
                    $message = __('Elasticsearch is not running.');
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        } else {
            $message = __('Elasticsearch library is not installed, please run the following :');
            $note = __('composer require elasticsearch/elasticsearch:~5.1');
        }

        $jsonData = json_encode([
            'message' => $message,
            'note' => $note,
            'success' => $success,
        ]);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Ktpl_SearchElastic::command_status');
    }
}
