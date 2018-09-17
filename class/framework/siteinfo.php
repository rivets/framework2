<?php
/**
 * A class that contains code to return info needed in various places on the site
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2018 Newcastle University
 *
 */
    namespace Framework;

    use \Support\Context as Context;
/**
 * Utility class that returns generally useful information about parts of the site
 */
    class SiteInfo
    {
        use \Framework\Utility\Singleton;
/**
 * Get beans in chunks and turn them one by one using a generator
 *
 * @param string    $bean   A bean name
 * @param string    $where  An SQL where condition
 * @param array     $params Substitutions for the where clause
 * @param int       $start  The start position
 * @param int       $count  The number wanted.
 *
 * @return void     But this yields beans
 */
        public function collect(string $bean, string $where, array $params, int $start, int $count)
        {
            if ($start !== '')
            {
                 $where .= ' LIMIT '.$count.' OFFSET '.(($start - 1)*$count);
            }
            $collection = R::findCollection($bean, $where, $params);
            while( $item = $collection->next() )
            {
                yield $item;
            }
        }
/**
 * Get bean data
 *
 * @param string    $bean       The bean kind
 * @param string    $where      The where condition
 * @param array     $params     Parameter values for query (if any)
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 *
 * @return array
 */
        public function fetch(string $bean, string $where, array $params, int $start, int $count) : array
        {
            if ($start !== '')
            {
                 $where .= ' LIMIT '.$count.' OFFSET '.(($start - 1)*$count);
            }
            if (empty($params))
            { // no offset and no params so use findAll
                 return \R::findAll($bean, $where);
            }
            return \R::find($bean, $where, $params);
        }
/**
 * Return the count for a particular bean
 *
 * @param string    $bean       The bean kind
 * @param string    $where      The where condition
 * @param array     $params     Parameter values for query (if any)
 *
 * @return int
 */
        public function count(string $bean, string $where = '', array $params = []) : int
        {
            return \R::count($bean, $where, $params);
        }
/**
 * Get all the user beans
 *
 * @param mixed     $start      Start position - used for pagination
 * @param mixed     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function users($start = '', $count = '', string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('user', $order !== '' ? $order : ' order by login', [], $start, $count);
        }
/**
 * Get all the page beans
 *
 * @param mixed     $start      Start position - used for pagination
 * @param mixed     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function pages($start = '', $count = '', string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('page', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the Rolename beans
 *
 * @param mixed     $start      Start position - used for pagination
 * @param mixed     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function roles($start = '', $count = '', string $order = '', $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('rolename', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the Rolecontext beans
 *
 * @param mixed     $start      Start position - used for pagination
 * @param mixed     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function contexts($start = '', $count = '', string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('rolecontext', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the site config information
 *
 * @param mixed     $start      Start position - used for pagination
 * @param mixed     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param boolean   $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function siteconfig($start = '', $count = '', string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('fwconfig', $order, [], $start, $count);
        }
/**
 * Get all the form beans
 *
 * @param mixed     $start      Start position - used for pagination
 * @param mixed     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function forms($start = '', $count = '', string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('form', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all users with a particular context/role
 * @param mixed     $rolecontext
 * @param mixed     $rolename
 * @param boolean   $all            If TRUE do not check if role is currentyl active
 * @param mixed     $start      Start position - used for pagination
 * @param mixed     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param boolean   $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function usersWith($rolecontext, $rolename, bool $all = FALSE, $start = '', $count = '', string $order = '', bool $collect = FALSE) : array
        {
            $context = Context::getinstance();
            $rnid = is_object($rolename) ? $rolename->getID() : $context->rolename($rolename)->getID();
            $rcid = is_object($rolecontext) ? $rolecontext->getID() : $context->rolecontext($rolecontext)->getID();
            $res = \R::findMulti('user', 'select user.* from user join role on (role.user_id = user.id) where rolename_id=? and rolecontext_id = ?'.
                ($all ? '' : ' and (start is NULL or start <= NOW()) and (end is NULL or end > NOW())'),
                [$rnid, $rcid]);
            return $res['user'];
        }
    }
?>
