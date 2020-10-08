<?php
/**
 * Contains definition of Plural class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2020 Newcastle University
 * @package Framework
 * @subpackage Utility
 */
    namespace Framework\Utility;

    class Plural extends \Twig\Extension\AbstractExtension
    {
/**
 * Returns the name of this extension
 *
 * Required by Twig
 *
 * @return string
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function getName() : string
        {
            return 'plural';
        }
/**
 * Returns the Twing functions this extension adds
 *
 * Required by Twig
 *
 * @return array
 * @psalm-suppress LessSpecificImplementedReturnType
 */
        public function getFunctions() : array
        {
            return [
                new \Twig\TwigFunction('splural', [$this, 'essify']),
                new \Twig\TwigFunction('plural', [$this, 'makePlural']),
            ];
        }
/**
 * Add an s if not 1
 *
 * @param int    $count
 * @param string $word
 *
 * @return string
 */
        public function essify(int $count, string $word) : string
        {
            return $count.' '.$word.($count != 1 ? 's' : '');
        }
/**
 * Do complex making plural.
 *
 * @param  int      $count  The number
 * @param  string   $one    The singular case
 * @param  string   $some   The not singular case
 * @param  ?string  $none   Special case for 0
 *
 * @return string
 */
        public function makePlural(int $count, string $one, string $some, $none = NULL) : string
        {
            switch ($count)
            {
            case 0:
                $res = $none ?? $some;
                break;
            case 1:
                $res = $one;
                break;
            default:
                $res = $some;
                break;
            }

            return sprintf($res, $count);
        }
    }
?>
