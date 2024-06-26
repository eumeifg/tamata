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

//@codingStandardsIgnoreFile
namespace Ktpl\ElasticSearch\Service\Stemming;

use Ktpl\ElasticSearch\Api\Service\Stemming\StemmerInterface;

/**
 * Class Ru
 *
 * @package Ktpl\ElasticSearch\Service\Stemming
 */
class Ru implements StemmerInterface
{
    /**
     * @var string
     */
    private $vowel = "аеёиоуыэюя";

    /**
     * @var array
     */
    private $regexPerfectiveGerunds = [
        "(в|вши|вшись)$",
        "(ив|ивши|ившись|ыв|ывши|ывшись)$",
    ];

    /**
     * @var string
     */
    private $regexAdjective = "(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|ую|юю|ая|яя|ою|ею)$";

    /**
     * @var array
     */
    private $regexParticiple = [
        "(ем|нн|вш|ющ|щ)",
        "(ивш|ывш|ующ)",
    ];

    /**
     * @var string
     */
    private $regexReflexives = "(ся|сь)$";

    /**
     * @var array
     */
    private $regexVerb = [
        "(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)$",
        "(ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)$",
    ];

    /**
     * @var string
     */
    private $regexNoun = "(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|ию|ью|ю|ия|ья|я)$";

    /**
     * @var string
     */
    private $regexSuperlative = "(ейш|ейше)$";

    /**
     * @var string
     */
    private $regexDerivational = "(ост|ость)$";

    /**
     * @var string
     */
    private $regexI = "и$";

    /**
     * @var string
     */
    private $regexNN = "нн$";

    /**
     * @var string
     */
    private $regexSoftSign = "ь$";

    /**
     * @var string
     */
    private $word = '';

    /**
     * @var int
     */
    private $RV = 0;

    /**
     * @var int
     */
    private $R2 = 0;

    /**
     * Singularize
     *
     * @param string $word
     * @return string
     */
    public function singularize($word)
    {
        mb_internal_encoding('UTF-8');
        $this->word = $word;
        $this->findRegions();
        //Шаг 1
        //Найти окончание PERFECTIVE GERUND. Если оно существует – удалить его и завершить этот шаг.
        if (!$this->removeEndings($this->regexPerfectiveGerunds, $this->RV)) {
            //Иначе, удаляем окончание REFLEXIVE (если оно существует).
            $this->removeEndings($this->regexReflexives, $this->RV);
            //Затем в следующем порядке пробуем удалить окончания: ADJECTIVAL, VERB, NOUN. Как только одно из них найдено – шаг завершается.
            if (!($this->removeEndings(
                    [
                        $this->regexParticiple[0] . $this->regexAdjective,
                        $this->regexParticiple[1] . $this->regexAdjective,
                    ],
                    $this->RV
                ) || $this->removeEndings($this->regexAdjective, $this->RV))
            ) {
                if (!$this->removeEndings($this->regexVerb, $this->RV)) {
                    $this->removeEndings($this->regexNoun, $this->RV);
                }
            }
        }
        //Шаг 2
        //Если слово оканчивается на и – удаляем и.
        $this->removeEndings($this->regexI, $this->RV);
        //Шаг 3
        //Если в R2 найдется окончание DERIVATIONAL – удаляем его.
        $this->removeEndings($this->regexDerivational, $this->R2);
        //Шаг 4
        //Возможен один из трех вариантов:
        //Если слово оканчивается на нн – удаляем последнюю букву.
        if ($this->removeEndings($this->regexNN, $this->RV)) {
            $this->word .= 'н';
        }
        //Если слово оканчивается на SUPERLATIVE – удаляем его и снова удаляем последнюю букву, если слово оканчивается на нн.
        $this->removeEndings($this->regexSuperlative, $this->RV);
        //Если слово оканчивается на ь – удаляем его.
        $this->removeEndings($this->regexSoftSign, $this->RV);
        return $this->word;
    }

    /**
     * Remove endings
     *
     * @param $regex
     * @param $region
     * @return bool
     */
    public function removeEndings($regex, $region)
    {
        $prefix = mb_substr($this->word, 0, $region, 'utf8');
        $word = substr($this->word, strlen($prefix));
        if (is_array($regex)) {
            if (preg_match('/.+[а|я]' . $regex[0] . '/u', $word)) {
                $this->word = $prefix . preg_replace('/' . $regex[0] . '/u', '', $word);
                return true;
            }
            $regex = $regex[1];
        }
        if (preg_match('/.+' . $regex . '/u', $word)) {
            $this->word = $prefix . preg_replace('/' . $regex . '/u', '', $word);
            return true;
        }
        return false;
    }

    /**
     * Find regions
     */
    private function findRegions()
    {
        $state = 0;
        $wordLength = mb_strlen($this->word, 'utf8');
        for ($i = 1; $i < $wordLength; $i++) {
            $prevChar = mb_substr($this->word, $i - 1, 1, 'utf8');
            $char = mb_substr($this->word, $i, 1, 'utf8');
            switch ($state) {
                case 0:
                    if ($this->isVowel($char)) {
                        $this->RV = $i + 1;
                        $state = 1;
                    }
                    break;
                case 1:
                    if ($this->isVowel($prevChar) && !$this->isVowel($char)) {
                        $state = 2;
                    }
                    break;
                case 2:
                    if ($this->isVowel($prevChar) && !$this->isVowel($char)) {
                        $this->R2 = $i + 1;
                        return;
                    }
                    break;
            }
        }
    }

    /**
     * Check if vowel
     *
     * @param $char
     * @return bool
     */
    private function isVowel($char)
    {
        return (strpos($this->vowel, $char) !== false);
    }
}
