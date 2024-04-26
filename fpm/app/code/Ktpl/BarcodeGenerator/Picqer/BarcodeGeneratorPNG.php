<?php

namespace Ktpl\BarcodeGenerator\Picqer;

use Imagick;
use imagickdraw;
use imagickpixel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Picqer\Barcode\Exceptions\BarcodeException;

class BarcodeGeneratorPNG extends \Picqer\Barcode\BarcodeGeneratorPNG
{
    /**
     * @var Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * BarcodeGeneratorPNG constructor.
     * @param Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    )
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Return a PNG image representation of barcode (requires GD or Imagick library).
     *
     * @param string $code code to print
     * @param string $type type of barcode:
     * @param int $widthFactor Width of a single bar element in pixels.
     * @param int $totalHeight Height of a single bar element in pixels.
     * @param array $color RGB (0-255) foreground color for bar elements (background is transparent).
     * @return string image data or false in case of error.
     * @public
     */
    public function getBarcode($fullcode, $type, $widthFactor = 3.5, $totalHeight = 60, $color = [0, 0, 0])
    {
        if(!is_array($fullcode)){
            $code = $fullcode;
            $codeForText = $fullcode;
        }else{
            $code = $fullcode['shipment_id'];
            $codeForText = $fullcode['suborder_id'];
        }

        $barcodeData = $this->getBarcodeData($code, $type);
        // calculate image size
        $width = ($barcodeData['maxWidth'] * $widthFactor);
        $height = $totalHeight;

        if (function_exists('imagecreate')) {
            // GD library
            $imagick = false;
            $png = imagecreate($width, $height);
            $colorBackground = imagecolorallocate($png, 255, 255, 255);
            imagecolortransparent($png, $colorBackground);
            $colorForeground = imagecolorallocate($png, $color[0], $color[1], $color[2]);
        } elseif (extension_loaded('imagick')) {
            $imagick = true;
            $colorForeground = new imagickpixel('rgb(' . $color[0] . ',' . $color[1] . ',' . $color[2] . ')');
            $png = new Imagick();
            $png->newImage($width, $height, 'none', 'png');
            $imageMagickObject = new imagickdraw();
            $imageMagickObject->setFillColor($colorForeground);
        } else {
            throw new BarcodeException('Neither gd-lib or imagick are installed!');
        }

        // print bars
        $positionHorizontal = 0;
        foreach ($barcodeData['bars'] as $bar) {
            $bw = round(($bar['width'] * $widthFactor), 3);
            $bh = round(($bar['height'] * $totalHeight / $barcodeData['maxHeight']), 3) - 30;
            if ($bar['drawBar']) {
                $y = round(($bar['positionVertical'] * $totalHeight / $barcodeData['maxHeight']), 3);
                // draw a vertical bar
                if ($imagick && isset($imageMagickObject)) {
                    $imageMagickObject->rectangle($positionHorizontal, $y, ($positionHorizontal + $bw), ($y + $bh));
                } else {
                    imagefilledrectangle(
                        $png,
                        $positionHorizontal,
                        $y,
                        ($positionHorizontal + $bw) - 1,
                        ($y + $bh),
                        $colorForeground
                    );
                }
            }
            $positionHorizontal += $bw;
        }

        ob_start();
        if ($imagick && isset($imageMagickObject)) {
            $png->drawImage($imageMagickObject);
            $png->annotateImage($imageMagickObject, 10, 45, 0, $code);
            echo $png;
        } else {
            $font = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)
                ->getAbsolutePath("app/code/Ktpl/BarcodeGenerator/view/base/web/css/fonts/cairo-400.ttf");

            /* Add text below barcode MAGE0913 */
            $png = $this->addBarcodeToImage($png, 15, 0, $font, $codeForText);
            imagepng($png);
            imagedestroy($png);
        }
        $image = ob_get_clean();
        return $image;
    }

    /**
     * @param $im
     * @param $font_size
     * @param $angle
     * @param $font
     * @param $text
     * @return mixed
     */
    public function addBarcodeToImage($im, $font_size, $angle, $font, $text)
    {
        // Get image Width and Height
        $image_width = imagesx($im);

        $text_box = imagettfbbox($font_size, $angle, $font, $text);

        $text_width = $text_box[2] - $text_box[0];

        $x = ($image_width / 2) - ($text_width / 2);

        $black = imagecolorallocate($im, 0, 0, 0);

        imagettftext($im, $font_size, 0, $x, 55, $black, $font, $text);

        return $im;
    }
}
