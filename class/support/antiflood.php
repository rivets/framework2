<?php
/**
 * A class that tries to detect when there have been too
 * many calls from a given IP address
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2020-2024 Newcastle University
 * @package Framework\Support
 */
    namespace Support;

    use \Config\Framework as FW;
/**
 * Handles AntiFlood calls
 */
    final class AntiFlood
    {
        private const int KEEPTIME     = 60*60;
        private const string DIVERSION = 'https://google.com';
/**
 * Check if an IP is flooding
 *
 * @param $limit    Number of seconds allowed between calls
 * @param $divert   If TRUE Then don't return but divert somewhere else
 */
        public static function flooding(int $limit, bool $divert = TRUE) : bool
        {
            $now = \time();
//
// First delete any flooding data that has expired
//
            \R::exec('delete from '.FW::FLOOD.' where ('.$now.' - calltime) > '.self::KEEPTIME);
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $f = \R::findOne(FW::FLOOD, 'ip=?', [$ip]);
            if (\is_object($f))
            {
                $res =  ($now - $f->calltime) < $limit;
            }
            else
            {
// Not in table so log it.
                $f = \R::dispense(FW::FLOOD);
                $f->ip = $ip;
                $res = FALSE;
            }
            $f->calltime = $now; // update the stored time
            \R::store($f);
            if ($divert && $res)
            { // we are flooding so divert the caller
                Context::getinstance()->web()->relocate(self::DIVERSION);
                /* NOT REACHED */
            }
            return $res;
        }
    }
?>