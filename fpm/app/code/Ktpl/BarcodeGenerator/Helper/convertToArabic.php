<?php

namespace Ktpl\BarcodeGenerator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class convertToArabic extends AbstractHelper
{

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    function convertArabicRightToLeft($text) {
        $splitted_array = explode(" ",$text);
        foreach($splitted_array as $key => $spa) {
            if($this->is_arabic($spa)) {
                $splitted_array[$key] = $this->converttext($spa);
            }
        }
        $new_arabic = join(' ', array_reverse($splitted_array));
        return $new_arabic;
    }

    function uniord($u) {
        // i just copied this function fron the php.net comments, but it should work fine!
        $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }

    function is_arabic($string) {

        // Initializing count variables with zero
        $arabicCount = 0;
        $englishCount = 0;
        // Getting the cleanest String without any number or Brackets or Hyphen
        $noNumbers = preg_replace('/[0-9]+/', '', $string);
        $noBracketsHyphen = array('(', ')', '-');
        $clean = trim(str_replace($noBracketsHyphen , '', $noNumbers));
        // After Getting the clean string, splitting it by space to get the total entered words
        $array = explode(" ", $clean); // $array contain the words that was entered by the user
        for ($i=0; $i <= count($array) ; $i++) {
            // Checking either word is Arabic or not
            $checkLang = preg_match('/\p{Arabic}/u', $string);
            if($checkLang == 1){
                ++$arabicCount;
            } else{
                ++$englishCount;
            }
        }
        if($arabicCount >= $englishCount){
            // Return 1 means TRUE i-e Arabic
            return 1;
        } else{
            // Return 0 means FALSE i-e English
            return 0;
        }

        /*$str = preg_replace('/[0-9]+/', '', $str);
        if(mb_detect_encoding($str) !== 'UTF-8') {
            $str = mb_convert_encoding($str,mb_detect_encoding($str),'UTF-8');
        }

        preg_match_all('/.|\n/u', $str, $matches);
        $chars = $matches[0];
        $arabic_count = 1;
        $latin_count = 0;
        $total_count = 1;
        foreach($chars as $char) {
            $pos = $this->uniord($char);
            //echo $char ." --> ".$pos.PHP_EOL;
            if($pos >= 1536 && $pos <= 1791) {
                $arabic_count++;
            } else if($pos > 123 && $pos < 123) {
                $latin_count++;
            }
            $total_count++;
        }
        if(($arabic_count/$total_count) > 1) {
            return true;
        }
        return false;*/
    }

    function converttext($part)
    {
        if (strlen($part) != strlen(utf8_decode($part)))
        {
            preg_match_all('/./us', $part, $ar);
            $part = join('', array_reverse($ar[0]));
        }

        return $part;

    }


}
