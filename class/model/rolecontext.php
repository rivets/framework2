<?php
/**
 * A model class for the RedBean object RoleContext
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-20120Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class implementing a RedBean model for RoleContext beans
 * @psalm-suppress UnusedClass
 */
    class RoleContext extends \RedBeanPHP\SimpleModel
    {
/**
 * Function called when a rolecontext bean is updated - do error checking in here
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function update() : void
        {
            if (!preg_match('/^[a-z][a-z0-9]*/i', $this->bean->name))
            {
                throw new \Framework\Exception\BadValue('Invalid context name');
            }
        }
/**
 * Add a RoleContext from a form - invoked by the AJAX bean operation
 *
 * @param Context  $context  The context object for the site
 *
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $p = \R::dispense(FW::ROLECONTEXT);
            $p->name = $context->formdata('post')->mustFetch('name');
            $p->fixed = 0;
            \R::store($p);
            return $p;
        }
    }
?>
