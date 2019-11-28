<?php
/**
 * A class that contains code to return info needed in various places on the site
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2019 Newcastle University
 *
 */
    namespace Framework;

    use \Support\Context as Context;
    use \Config\Framework as FW;
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
            FW::CONFIG,
            FW::CONFIRM,
            FW::FORM,
            FW::FORMFIELD,
            FW::PAGE,
            FW::PAGEROLE,
            FW::ROLE,
            FW::ROLECONTEXT,
            FW::ROLENAME,
            FW::USER,
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
 * @psalm-return \Generator<mixed, mixed, mixed, void>    But this yields beans
 * @return mixed
 */
        public function collect(string $bean, string $where, array $params = [], int $start = -1, int $count = 0)
        {
            if ($start >= 0)
            {
                 $where .= ' LIMIT '.$count.' OFFSET '.(($start - 1)*$count);
            }
            $collection = \R::findCollection($bean, $where, $params);
            /** @psalm-suppress InvalidMethodCall - not sure why psalm gives an error here */
            while ($item = $collection->next())
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
        public function fetch(string $bean, string $where, array $params = [], int $start = -1, int $count = 0) : array
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
            return $this->{$collect ? 'collect' : 'fetch'}(FW::USER, $order !== '' ? $order : ' order by login', [], $start, $count);
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
            return $this->{$collect ? 'collect' : 'fetch'}(FW::PAGE, $order !== '' ? $order : ' order by name', [], $start, $count);
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
            return $this->{$collect ? 'collect' : 'fetch'}(FW::ROLENAME, $order !== '' ? $order : ' order by name', [], $start, $count);
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
            return $this->{$collect ? 'collect' : 'fetch'}(FW::ROLECONTEXT, $order !== '' ? $order : ' order by name', [], $start, $count);
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
            return $this->{$collect ? 'collect' : 'fetch'}(FW::CONFIG, $order, [], $start, $count);
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
            return $this->{$collect ? 'collect' : 'fetch'}(FW::FORM, $order !== '' ? $order : ' order by name', [], $start, $count);
        }
/**
 * Get a specific form
 *
 * @param string       $name     The name of the form
 *
 * @return ?object
 */
        public function form(string $name) : ?object
        {
            return \R::findOne(FW::FORM, 'name=?', [$name]);
        }
/**
 * Get all users with a particular context/role
 * @param mixed     $rolecontext
 * @param mixed     $rolename
 * @param bool      $all            If TRUE do not check if role is currentyl active
 * @param int       $start      Start position - used for pagination
 * @param int       $count      The number to be fetched - used for pagination
 * @param string    $order      An order clause
 * @param bool      $collect    If TRUE then use collect not fetch
 *
 * @return array
 */
        public function usersWith($rolecontext, $rolename, bool $all = FALSE, int $start = -1, int $count = -1, string $order = '', bool $collect = FALSE) : array
        {
            $context = Context::getinstance();
            $rnid = is_object($rolename) ? $rolename->getID() : $context->rolename($rolename)->getID();
            $rcid = is_object($rolecontext) ? $rolecontext->getID() : $context->rolecontext($rolecontext)->getID();
            $res = \R::findMulti(FW::USER, 'select '.FW::USER.'.* from '.FW::USER.' join '.FW::ROLE.' on ('.FW::ROLE.
                '.'.FW::USER.'_id = '.FW::USER.'.id) where '.FW::ROLENAME.'_id=? and '.FW::ROLECONTEXT.'_id = ?'.
                ($all ? '' : ' and (start is NULL or start <= NOW()) and (end is NULL or end > NOW())'),
                [$rnid, $rcid]);
            return $res['user'];
        }
 /**
  * Check to see if a table exists - utility function used by AJAX
  *
  * @param string $table
  *
  * @return bool
  */
        public static function tableExists($table)
        {
            $tbs = \R::inspect();
            return in_array(strtolower($table), $tbs);
        }
 /**
  * Check to see if a table has a given field
  *
  * @param string $table
  * @parm string $field
  *
  * @return bool
  */
        public static function hasField($table, $field)
        {
            $tbs = \R::inspect($table);
            return isset($tbs[$field]);
        }
/**
 * Check if table is a framework table
 *
 * @param string $table
 *
 * @return bool
 */
        public static function isFWTable($table)
        {
            return in_array($table, self::$fwtables);
        }
/**
 * Return bean table data
 *
 * @param bool    $all  If TRUE then return all beans, otherwise just non-framework beans.
 *
 * @return array
 */
        public function tables($all = FALSE) : array
        {
            $beans = [];
            foreach(\R::inspect() as $tab)
            {
                if ($all || !self::isFWTable($tab))
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
        public function pagecount($table, $pagesize) : int
        {
            return (int) floor((\R::count($table) + $pagesize) / $pagesize);
        }
    }
?>