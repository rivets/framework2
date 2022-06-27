<?php
/**
 * A class that contains code to support returnng info needed in various places on the site
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2021 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \R;
    use \Support\Context;
/**
 * Base for the utility class that returns generally useful information about parts of the site
 */
    class SiteInfoBase
    {
        use \Framework\Utility\Singleton;

        protected Context $context;
/**
 * Class constructor. The concrete class using this trait can override it.
 *
 * @psalm-suppress PropertyTypeCoercion
 */
        protected function __construct()
        {
            $this->context = Context::getinstance();
        }
/**
 * Get beans in chunks and turn them one by one using a generator
 *
 * @param string            $bean   A bean name
 * @param string            $where  An SQL where condition
 * @param array<mixed>      $params Substitutions for the where clause
 * @param int               $start  The start position
 * @param int               $count  The number wanted.
 *
 * @psalm-return \Generator<mixed, mixed, mixed, void>    But this yields beans
 */
        public function collect(string $bean, string $where, array $params = [], int $start = -1, int $count = 0) : \Generator
        {
            if ($start >= 0)
            {
                 $where .= ' LIMIT '.$count.' OFFSET '.(($start - 1)*$count);
            }
            R::getPDO()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, FALSE);
            $collection = R::findCollection($bean, $where, $params);
            /** @psalm-suppress InvalidMethodCall - not sure why psalm gives an error here */
            while ($item = $collection->next())
            {
                yield $item;
            }
        }
/**
 * Get bean data
 *
 * @param string            $bean       The bean kind
 * @param string            $where      The where condition
 * @param array<mixed>      $params     Parameter values for query (if any)
 * @param int               $start      Start position - used for pagination
 * @param int               $count      The number to be fetched - used for pagination
 */
        public function fetch(string $bean, string $where, array $params = [], int $start = -1, int $count = 0) : array
        {
            if ($start >= 0)
            {
                 $where .= ' LIMIT '.$count.' OFFSET '.(($start - 1)*$count);
            }
            if (empty($params))
            { // no offset and no params so use findAll
                 return R::findAll($bean, $where);
            }
            return R::find($bean, $where, $params);
        }
/**
 * Return the count for a particular bean
 *
 * @param string            $bean       The bean kind
 * @param string            $where      The where condition
 * @param array<mixed>      $params     Parameter values for query (if any)
 */
        public function count(string $bean, string $where = '', array $params = []) : int
        {
            return R::count($bean, $where, $params);
        }

/**
 * Do a page count calculation for a table
 *
 * @param string            $table
 * @param int               $pagesize
 * @param string            $where
 * @param array<mixed>      $params
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function pageCount(string $table, int $pagesize = 10, string $where = '', array $params = []) : int
        {
            $count = R::count($table, $where, $params);
            return (int) \floor(($count % $pagesize > 0 ? ($count + $pagesize) : $count) / $pagesize);
        }
    }
?>