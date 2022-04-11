<?php
/**
 * A class for dealing with complex updates
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2022 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Config;

    //use \Support\Context;
/**
 * A class Update object
 * @psalm-suppress UnusedClass
 */
    class Update
    {
/**
 * Apply any updates that need to change the database name etc.
 * Normally will do nothing.
 * There needs to be some kind of exclusion in place to stop things being applied multiple times!!
 */
        static public function apply() : void
        {
            // $context = Context::getInstance();
        }
/*
        private static function addBean(string $name, array $fields = [])
        {
            $bn = \R::dispense($name);
            foreach ($fields as $field => $sampleValue)
            {
                $bn->{$field} = $sampleValue;
            }
            \R::store($bn);
            \R::trash($bn); // get rid of the temporary bean
        }
        private static function renameBean(string $from, string $to)
        {
            \R::exec('rename table `'.$from.'` to `'.$to.'`');
        }
        private static function makeFK(string $from, string $to)
        {
            $qw = \R::getWriter();
        }
*/
    }
?>