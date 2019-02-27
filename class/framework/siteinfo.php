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
    use \Config\Config as Config;
/**
 * Utility class that returns generally useful information about parts of the site
 */
    class SiteInfo
    {
        use \Framework\Utility\Singleton;
/**
 * @var array  Array of the names of the beans used by the framework
 */
        private static $fwtables = [
            Config::CONFIG,
            'confirm',
            'page',
            'pagerole',
            'role',
            'rolecontext',
            'rolename',
            'user',
        ];
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
            if ($start >= 0)
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
            if ($start >= 0)
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
 * @param int     $start      Start position - used for pagination
 * @param int     $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function users(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('user', $order !== '' ? $order : ' order by login', [], $start, $count);
        }
/**
 * Get all the page beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function pages(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('page', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the Rolename beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function roles(int $start = -1, int $count = -1, string $order = '', $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('rolename', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the Rolecontext beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function contexts(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('rolecontext', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get all the site config information
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool          $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function siteconfig(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}(Config::CONFIG, $order, [], $start, $count);
        }
/**
 * Get all the form beans
 *
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function forms(int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            return $this->{$collect ? 'collect' : 'fetch'}('form', $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get a specific form
 *
 * @param string       $name     The name of the form
 *
 * @return object
 */
        public function form(string $name)
        {
            return \R::findOne('form', 'name=?', [$name]);
        }
/**
 * Get all users with a particular context/role
 * @param mixed     $rolecontext
 * @param mixed     $rolename
 * @param bool          $all            If TRUE do not check if role is currentyl active
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool          $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function usersWith($rolecontext, $rolename, bool $all = FALSE, int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            $context = Context::getinstance();
            $rnid = is_object($rolename) ? $rolename->getID() : $context->rolename($rolename)->getID();
            $rcid = is_object($rolecontext) ? $rolecontext->getID() : $context->rolecontext($rolecontext)->getID();
            $res = \R::findMulti('user', 'select user.* from user join role on (role.user_id = user.id) where rolename_id=? and rolecontext_id = ?'.
                ($all ? '' : ' and (start is NULL or start <= NOW()) and (end is NULL or end > NOW())'),
                [$rnid, $rcid]);
            return $res['user'];
        }
/**
 * Return bean table data
 *
 * @param boolean    $all  If TRUE then return all beans, otehrwise just non-framework beans.
 *
 * @return array
 */
        public function tables($all = FALSE)
        {
            $beans = [];
            foreach(\R::inspect() as $tab)
            {
                if ($all || !in_array($tab, self::$fwtables))
                {
                    $beans[] = new \Support\Table($tab);
                }
            }
            return $beans;
        }
/**
 * Do a page count calculation for a table
 *
 * @param string    $table
 * @param int       $pagesize
 *
 * @return int
 */
        public function pagecount($table, $pagesize)
        {
            return (int) floor((\R::count($table) + $pagesize) / $pagesize);
        }
    }
?>
