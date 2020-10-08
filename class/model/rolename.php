<?php
/**
 * A model class for the RedBean object RoleName
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2020 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Config\Framework as FW;
    use Support\Context;
/**
 * A class implementing a RedBean model for RoleName beans
 * @psalm-suppress UnusedClass
 */
    class RoleName extends \RedBeanPHP\SimpleModel
    {
/**
 * Function called when a rolename bean is updated - do error checking in here
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function update() : void
        {
            if (!preg_match('/^[a-z][a-z0-9]*/i', $this->bean->name))
            {
                throw new \Framework\Exception\BadValue('Invalid role name');
            }
        }
/**
 * Add a RoleName from a form
 *
 * @see Framework\Ajax::bean
 *
 * @param Context  $context  The Context object for the site
 *
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $p = \R::dispense(FW::ROLENAME);
            $p->name = $context->formdata('post')->mustFetch('name');
            $p->fixed = 0;
            \R::store($p);
            return $p;
        }
    }
?>
