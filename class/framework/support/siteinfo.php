<?php
/**
 * A class that contains code to return info needed in various places on the site
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2021 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Config\Framework as FW;
    use \R;
/**
 * Utility class that returns generally useful information about parts of the site
 */
    class SiteInfo extends SiteInfoBase
    {
        use SITable; // bring in table handling methods
/**
 * Get all the user beans
 *
 * @param int     $start      Start position - used for pagination
 * @param int     $count      The number to be fetched - used for pagination
 * @param string  $order      An order clause
 * @param bool    $collect    If TRUE then use collect not fetch
 */
        public function users(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}(FW::USER, $order !== '' ? $order : ' order by login', [], $start, $count);
        }
/**
 * Get all the page beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 */
        public function pages(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}(FW::PAGE, $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the Rolename beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 */
        public function roles(int $start = -1, int $count = -1, string $order = '', $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}(FW::ROLENAME, $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the Rolecontext beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 */
        public function contexts(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}(FW::ROLECONTEXT, $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the site config information
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool          $collect    If TRUE then use collect not fetch
 */
        public function siteConfig(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}(FW::CONFIG, $order, [], $start, $count);
        }
/**
 * Get all the form beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 */
        public function forms(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}(FW::FORM, $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get a specific form
 *
 * @param string       $name     The name of the form
 */
        public function form(string $name) : ?\RedBeanPHP\OODBBean
        {
            return R::findOne(FW::FORM, 'name=?', [$name]);
        }
/**
 * Get all users with a particular context/role
 * @param bool                          $all        If TRUE do not check if role is currentyl active
 * @param int                           $start      Start position - used for pagination
 * @param int                           $count      The number to be fetched - used for pagination
 * @param string                        $order      An order clause
 * @param bool                          $collect    If TRUE then use collect not fetch
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function usersWith(string|\RedBeanPHP\OODBBean $rolecontext, string|\RedBeanPHP\OODBBean $rolename, bool $all = FALSE,
            int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            $rnid = \is_object($rolename) ? $rolename->getID() : $this->context->rolename($rolename)->getID();
            $rcid = \is_object($rolecontext) ? $rolecontext->getID() : $this->context->rolecontext($rolecontext)->getID();
            $res = R::findMulti(FW::USER, 'select '.FW::USER.'.* from '.FW::USER.' join '.FW::ROLE.' on ('.FW::ROLE.
                '.'.FW::USER.'_id = '.FW::USER.'.id) where '.FW::ROLENAME.'_id=? and '.FW::ROLECONTEXT.'_id = ?'.
                ($all ? '' : ' and (start is NULL or start <= NOW()) and (end is NULL or end > NOW())'),
                [$rnid, $rcid]);
            return $res['user'];
        }
    }
?>