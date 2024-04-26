<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Model\ImageLabel;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;

class FileInfo
{
    /**
     * Path in /pub/media directory
     */
    const ENTITY_MEDIA_PATH = '/ktpl_productlabel/imagelabel';

    private $filesystem;

    private $mime;

    private $mediaDirectory;

    private $baseDirectory;

    public function __construct(
        Filesystem $filesystem,
        Mime $mime
    ) {
        $this->filesystem = $filesystem;
        $this->mime       = $mime;
    }

    public function getMimeType($fileName)
    {
        $filePath         = $this->getFilePath($fileName);
        $absoluteFilePath = $this->getMediaDirectory()->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);

        return $result;
    }

    public function getStat($fileName)
    {
        $filePath = $this->getFilePath($fileName);

        $result = $this->getMediaDirectory()->stat($filePath);

        return $result;
    }

    public function isExist($fileName)
    {
        $filePath = $this->getFilePath($fileName);
        $result   = $this->getMediaDirectory()->isExist($filePath);

        return $result;
    }

    public function isBeginsWithMediaDirectoryPath($fileName)
    {
        $filePath = ltrim($fileName, '/');

        $mediaDirectoryRelativeSubpath          = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath();
        $isFileNameBeginsWithMediaDirectoryPath = strpos($filePath, $mediaDirectoryRelativeSubpath) === 0;

        return $isFileNameBeginsWithMediaDirectoryPath;
    }

    private function getFilePath($fileName)
    {
        $filePath = ltrim($fileName, '/');

        $mediaDirectoryRelativeSubpath          = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath();
        $isFileNameBeginsWithMediaDirectoryPath = $this->isBeginsWithMediaDirectoryPath($fileName);

        // If the file is not using a relative path, it resides in the catalog/category media directory.
        $fileIsInCategoryMediaDir = !$isFileNameBeginsWithMediaDirectoryPath;

        if ($fileIsInCategoryMediaDir) {
            $filePath = self::ENTITY_MEDIA_PATH . '/' . $filePath;
        } else {
            $filePath = substr($filePath, strlen($mediaDirectoryRelativeSubpath));
        }

        return $filePath;
    }

    private function getMediaDirectoryPathRelativeToBaseDirectoryPath()
    {
        $baseDirectoryPath  = $this->getBaseDirectory()->getAbsolutePath();
        $mediaDirectoryPath = $this->getMediaDirectory()->getAbsolutePath();

        $mediaDirectoryRelativeSubpath = substr($mediaDirectoryPath, strlen($baseDirectoryPath));

        return $mediaDirectoryRelativeSubpath;
    }

    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }

    private function getBaseDirectory()
    {
        if (!isset($this->baseDirectory)) {
            $this->baseDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        }

        return $this->baseDirectory;
    }
}
