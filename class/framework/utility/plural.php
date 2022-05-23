<?php
/**
 * Contains definition of Plural class used for a Twig extension
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
 * @return array<\Twig\TwigFunction>
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
 */
        public function makePlural(int $count, string $one, string $some, ?string $none = NULL) : string
        {
            $res = match ($count) {
                0 => $none ?? $some,
                1 => $one,
                default => $some,
            };
            return sprintf($res, $count);
        }
    }
?>
