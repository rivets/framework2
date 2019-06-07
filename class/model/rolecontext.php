<?php
/**
 * A model class for the RedBean object RoleContext
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2019 Newcastle University
 *
 */
    namespace Model;

    use \Support\Context as Context;
    use \Config\Framework as FW;
/**
 * A class implementing a RedBean model for RoleContext beans
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
 * @param \Support\Context	$context	The context object for the site
 *
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $p = \R::dispense(FW::ROLECONTEXT);
            $p->name = $context->formdata()->mustpost('name');
            $p->fixed = 0;
            \R::store($p);
            return $p;
        }
    }
?>