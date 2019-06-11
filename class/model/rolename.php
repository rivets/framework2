<?php
/**
 * A model class for the RedBean object RoleName
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2019 Newcastle University
 *
 */
    namespace Model;

    use Support\Context as Context;
    use \Config\Framework as FW;
/**
 * A class implementing a RedBean model for RoleName beans
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
 * @param \Support\Context	$context	The Context object for the site
 *
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $p = \R::dispense(FW::ROLENAME);
            $p->name = $context->formdata()->mustpost('name');
            $p->fixed = 0;
            \R::store($p);
            return $p;
        }
    }
?>