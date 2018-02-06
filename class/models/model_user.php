<?php
/**
 * A model class for the RedBean object User
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013-2018 Newcastle University
 *
 */
/**
 * A class implementing a RedBean model for User beans
 */
    class Model_User extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string   The type of the bean that stores roles for this page
 */
        private $roletype = 'role';

        use \ModelExtend\User;

        use \Framework\HandleRole;
/**
 * @var Array   Key is name of field and the array contains flags for checks
 */
        private static $editfields = [
            'email'     => [TRUE],         # [NOTEMPTY]
        ];
/**
 * Is this user an admin?
 *
 * @return boolean
 */
        public function isadmin()
        {
            return is_object($this->hasrole('Site', 'Admin'));
        }
/**
 * Is this user active?
 *
 * @return boolean
 */
        public function isactive()
        {
            return $this->bean->active;
        }
/**
 * Is this user confirmed?
 *
 * @return boolean
 */
        public function isconfirmed()
        {
            return $this->bean->confirm;
        }
/**
 * Is this user a developer?
 *
 * @return boolean
 */
        public function isdeveloper()
        {
            return is_object($this->hasrole('Site', 'Developer'));
        }
/**
 * Set the user's password
 *
 * @param string	$pw	The password
 *
 * @return void
 */
        public function setpw($pw)
        {
            $this->bean->password = password_hash($pw, PASSWORD_DEFAULT);
            \R::store($this->bean);
        }
/**
 * Check a password
 *
 * @param string	$pw The password
 *
 * @return boolean
 */
        public function pwok($pw)
        {
            return password_verify($pw, $this->bean->password);
        }
/**
 * Set the email confirmation flag
 *
 * @return void
 */
        public function doconfirm()
        {
            $this->bean->active = 1;
            $this->bean->confirm = 1;
            \R::store($this->bean);
        }
/**
 * Generate a token for this user that can be used as a unique id from a phone.
 *
 * @param string    $device     Currently not used!!
 *
 * @return string
 */
	public function maketoken($device = '')
	{
	    $token = (object)['iss' => \Config\Config::SITEURL, 'iat' => idate('U'), 'sub' => $this->bean->getID()];
	    return JWT::encode($token, \Framework\Context::KEY);
	}
/**
 * Handle an edit form for this user
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
                $val = $fdt->post($fld, '');
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
            $pw = $fdt->post('pw', '');
            if ($pw !== '')
            {
                if ($pw == $fdt->post('rpw', ''))
                {
                    $this->setpw($pw); // setting the password will do a store
                }
                else
                {
                    $error = TRUE;
                }
            }
            $this->editroles($context);
//            $uroles = $this->roles();
//	    if ($fdt->haspost('exist'))
//	    {
//                foreach ($_POST['exist'] as $ix => $rid)
//                {
//                    $rl = $context->load('role', $rid);
//                    $start = $_POST['xstart'][$ix];
//                    $end = $_POST['xend'][$ix];
//                    $other = $_POST['xotherinfo'][$ix];
//                    if (strtolower($start) == 'now')
//                    {
//                        $rl->start = $context->utcnow();
//                    }
//                    elseif ($start != $rl->start)
//                    {
//                        $rl->start = $context->utcdate($start);
//                    }
//                    if (strtolower($end) == 'never' || $end === '')
//                    {
//                        if ($rl->end !== '')
//                        {
//                            $rl->end = NULL;
//                        }
//                    }
//                    elseif ($end != $rl->end)
//                    {
//                         $rl->end = $context->utcdate($end);
//                    }
//                    if ($other != $rl->otherinfo)
//                    {
//                        $rl->otherinfo = $other;
//                    }
//                    \R::store($rl);
//                }
//	    }
//            foreach ($_POST['role'] as $ix => $rn)
//            {
//                $cn = $_POST['context'][$ix];
//                if ($rn !== '' && $cn !== '')
//                {
//                    $end = $_POST['end'][$ix];
//                    $start = $_POST['start'][$ix];
//                    $this->addrolebybean($context->load('rolecontext', $cn), $context->load('rolename', $rn), $_POST['otherinfo'][$ix],
//                        strtolower($start) == 'now' ? $context->utcnow() : $context->utcdate($start),
//                        strtolower($end) == 'never' || $end === '' ? '' : $context->utcdate($end)
//                    );
//                }
//            }
            return TRUE;
        }
    }
?>
