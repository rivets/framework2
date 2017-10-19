<?php
/**
 * A model class for the RedBean object Page
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017 Newcastle University
 *
 */
/**
 * A class implementing a RedBean model for Page beans
 */
/**
 * @var string   The type of the bean that stores roles for this page
 */
    class Model_Page extends \RedBeanPHP\SimpleModel
    {
        private $roletype = 'pagerole';

        use \Framework\HandleRole;
/**
 * @var Array   Key is name of field and the array contains flags for checks
 */
        private static $editfields = array(
            'name'          => array(TRUE, FALSE),   
            'kind'          => array(TRUE, FALSE),  
            'source'        => array(TRUE, FALSE),
            'active'        => array(TRUE, TRUE),
            'mobileonly'    => array(TRUE, TRUE),
            'needlogin'     => array(TRUE, TRUE),
        );
/**
 * Check user can access the page
 *
 * @param object    $context    The context object
 *
 * @return boolean
 */
        public function check($context)
        {
            if ($this->bean->needlogin)
            {
                if (!$context->hasuser())
                { # not logged in
                    $context->divert('/login?page='.urlencode($context->local()->debase($_SERVER['REQUEST_URI'])));
                    /* NOT REACHED */
                }
                $match = \R::getCell('select count(p.id) = count(r.id) from user as u inner join role as r on u.id = r.user_id inner join '.
                    '(select * from pagerole where page_id=?) as p on p.rolename_id = r.rolename_id and p.rolecontext_id = r.rolecontext_id where u.id=?',
                    [$this->bean->getID(), $context->user()->getID()]);
                if (!$match ||                              // User does not have all the required roles
                    ($this->bean->mobileonly && !$context->hastoken()))	// not mobile and logged in
                {
                    $context->web()->sendstring($context->local()->getrender('error/403.twig'), \Framework\Web\Web::HTMLMIME, \Framework\Web\StatusCodes::HTTP_FORBIDDEN);
                    exit;
                }
           }
        }
/**
 * Handle an edit form for this page
 *
 * @param object   $context    The context object
 *
 * @return void
 */
        public function edit($context)
        {
            $change = FALSE;
            $error = FALSE;
            $fdt = $context->formdata();
            foreach (self::$editfields as $fld => $flags)
            { // might need more fields for different applications
                $val = $fdt->post($fld, $flags[1] ? 0 : ''); # might be a flag
                if ($flags[0] && $val === '')
                { // this is an error as this is a required field
                    $error = TRUE;
                }
                elseif ($val != $this->bean->$fld)
                {
                    $this->bean->$fld = $val;
                    $change = TRUE;
                }
            }
            if ($change)
            {
                \R::store($this->bean);
            }
            $this->editroles($context);
            $admin = $this->hasrole('Site', 'Admin');
            if (is_object($devel = $this->hasrole('Site', 'Developer')) && !is_object($admin))
            { // if we need developer then we also need admin
                $admin = $this->addrole('Site', 'Admin', '-', $devel->start, $devel->end);
            }
            if (is_object($admin) && !$this->bean->needlogin)
            { // if we need admin then we also need login!
                $this->bean->needlogin = 1;
                \R::store($this->bean);
            }
            return TRUE;
        }
    }
?>
