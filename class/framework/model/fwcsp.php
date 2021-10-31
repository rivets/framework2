<?php
/**
 * A model class for the RedBean object FWConfig
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! This is a Framework system class - do not edit !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2021 Newcastle University
 * @package Framework\Model
 */
    namespace Framework\Model;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class implementing a RedBean model for Page beans
 * @psalm-suppress UnusedClass
 */
    final class FWcsp extends \RedBeanPHP\SimpleModel
    {
/**
 * Add a new FWConfig bean
 *
 * @see Framework\Ajax::bean
 *
 * @throws \Framework\Exception\BadValue
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $fdt = $context->formdata('post');
            $type = $fdt->mustFetch('type');
            $host = $fdt->mustFetch('host');
            $bn = \R::findOne(FW::CSP, 'type=? and host=?', [$type, $host]);
            if (\is_object($bn))
            {
                throw new \Framework\Exception\BadValue('CSP item exists');
            }
            $bn = \R::dispense(FW::CSP);
            $bn->type = $type;
            $bn->host = $host;
            $bn->essential = $fdt->mustFetch('essential');
            \R::store($bn);
            return $bn;
        }
    }
?>