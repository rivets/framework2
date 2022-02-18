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

    use \Support\Context;
/**
 * A class Update object
 * @psalm-suppress UnusedClass
 */
    class Update
    {
/**
 * Apply any updates that need to change the database name etc.
 * Normally will do nothing.
 */
        public function apply() : void
        {
        }
    }
?>