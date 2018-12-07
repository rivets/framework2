<?php
/**
 * A model class for the RedBean object RoleName
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018 Newcastle University
 *
 */
    namespace Model;

    use Support\Context as Context;
/**
 * A class implementing a RedBean model for RoleName beans
 */
    class RoleName extends \RedBeanPHP\SimpleModel
    {
/**
 * Add a RoleName from a form - invoked by the AJAX bean operation
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public static function add(Context $context)
        {
            $p = R::dispense('rolename');
            $p->name = $context->formdata()->mustpost('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
    }
?>