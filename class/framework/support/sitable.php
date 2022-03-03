<?php
/**
 * A trait that implements the table functions for siteinfo
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2021 Newcastle University
 * @package Framework
 */
    namespace Framework\Support;

    use \Config\Framework as FW;
    use \R;
/**
 * Adds functions for manipulating tables
 */
    trait SITable
    {
        private static array $fields = []; // Field information for tables
/**
 * Check to see if a table exists - utility function used by AJAX
 */
        public static function tableExists(string $table) : bool
        {
            return \in_array(\strtolower($table), R::inspect());
        }
 /**
  * Check to see if a table has a given field
  */
        public static function hasField(string $table, string $field) : bool
        {
            if (!isset(self::$fields[$table]))
            {
                self::$fields[$table] = R::inspect($table);
            }
            return isset(self::$fields[$table][$field]);
        }
/**
 * Return bean table data
 *
 * @param bool    $all  If TRUE then return all beans, otherwise just non-framework beans.
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function tables(bool $all = FALSE, int $start = -1, int $count = -1) : array
        {
            $beans = [];
            foreach(R::inspect() as $tab)
            {
                if ($all || !FW::isFWTable($tab))
                {
                    $beans[] = new \Framework\Support\FWTable($tab);
                }
            }
            return $start < 0 ? $beans : \array_slice($beans, ($start - 1) * $count, $count);
        }
    }
?>