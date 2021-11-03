<?php


namespace Inn20\PhpVirus;

use PhpParser\ParserFactory;
use PhpParser\Node;

class Common
{

    /**
     * @param $string
     * @return Node|null
     */
    public static function stringNToFuncN($string)
    {
        $binString = base64_encode($string);
        $code = <<<EOF
            <?php
            base64_decode('$binString');
EOF;
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse(trim($code));
        /** @var Node\Stmt\Expression $newNode */

        $node = $ast[0];

        return $node->expr;

    }

    static $varName = '123';

    static public function generateVarName()
    {
        self::$varName = md5(self::$varName);
        return sprintf('v%s', self::$varName);
    }

    /**
     * @param $str
     * @return string
     * Thinks for https://github.com/pk-fr/yakpro-po
     */
    public static function obfuscateString($str)
    {
        $l = strlen($str);
        $result = '';
        for ($i = 0; $i < $l; ++$i) {
            $result .= mt_rand(0, 1) ? "\x" . dechex(ord($str[$i])) : "\\" . decoct(ord($str[$i]));
        }
        return $result;
    }

}
