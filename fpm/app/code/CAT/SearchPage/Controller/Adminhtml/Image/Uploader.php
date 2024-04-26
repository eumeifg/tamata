<?php

namespace CAT\SearchPage\Controller\Adminhtml\Image;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ImageUploader;

class Uploader extends \Magento\Backend\App\Action
{
    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @param Action\Context $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Action\Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    public function execute() {
        $docs = [];
        $dynamicRows = isset($_FILES['dynamic_rows']) ? $_FILES['dynamic_rows'] : [];

        foreach ($dynamicRows as $key => $dynamicRow){
            foreach ($dynamicRow as $doc){
                if($key == 'name'){
                    $docs['name'] = $doc['banner'];
                }
                if($key == 'type'){
                    $docs['type'] = $doc['banner'];
                }
                if($key == 'tmp_name'){
                    $docs['tmp_name'] = $doc['banner'];
                }
                if($key == 'error'){
                    $docs['error'] = $doc['banner'];
                }
                if($key == 'size'){
                    $docs['size'] = $doc['banner'];
                }
            }
        }
        if(!empty($docs)){
            try {
                $result = $this->imageUploader->saveFileToTmpDir($docs);
                $result['cookie'] = [
                    'name' => $this->_getSession()->getName(),
                    'value' => $this->_getSession()->getSessionId(),
                    'lifetime' => $this->_getSession()->getCookieLifetime(),
                    'path' => $this->_getSession()->getCookiePath(),
                    'domain' => $this->_getSession()->getCookieDomain(),
                ];
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
        }
    }
}
