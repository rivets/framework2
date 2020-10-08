<?php
/**
 * A class for the object Table
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2020 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Support\Context;
/**
 * A class Table object
 * @psalm-suppress UnusedClass
 */
    class Table
    {
/**
 * @var string The name of the table
 */
        private $table;

        use \ModelExtend\MakeGuard;
/**
 * Constructor
 *
 * @param string $name The name of the table
 */
        public function __construct(string $name)
        {
            if (!\Support\SiteInfo::tableExists($name))
            {
                throw new \Framework\Exception\BadValue('Table does not exist');
                /* NOT REACHED */
            }
            $this->table = $name;
        }
/**
 * Process new bean
 *
 * @internal
 * @param Context   $context    The context object
 * @param string    $bean       The bean type
 *
 * @return void
 */
        private static function makebean(Context $context, string $bean) : void
        {
            $fk = [];
            $fdt = $context->formdata('post');
            $bn = \R::dispense($bean);
            foreach ($fdt->fetchArray('field') as $ix => $field)
            {
                if ($field !== '')
                {
                    if (preg_match('/^([a-z][a-z0-9]*)_id/', $field, $m))
                    { // this is a special case for foreign keys
                        $fkbn = \R::dispense($m[1]); // make a bean of the required type
                        \R::store($fkbn);
                        $bn->{$field} = $fkbn;
                        $fk[] = $fkbn;  // remember this bean as it needs to be deleted later - see below
                    }
                    elseif (!preg_match('/^[a-z][a-z0-9]*/', $field))
                    {
                        $context->local()->message(\Framework\Local::ERROR, 'Field names must be alphanumeric: '.$field.' not stored');
                    }
                    else
                    {
                        $bn->{$field} = $fdt->fetch(['sample', $ix], '');
                    }
                }
            }
            \R::store($bn);
            \R::exec('truncate '.$bean); // clean out the table
            if (!empty($fk))
            { // get rid of any extra beans we created for foreign keys
                \R::trashAll($fk);
            }
            $context->local()->message(\Framework\Local::MESSAGE, $bean.' created');
        }
/**
 * Add a new table
 *
 * @param Context    $context  The context object
 *
 * @return bool
 */
        public static function add(Context $context) : bool
        {
            $fdt = $context->formdata('post');
            if ($fdt->exists('name'))
            {
                $name = strtolower($fdt->mustFetch('name'));
                if ($name === '' || !preg_match('/^[a-z][a-z0-9]*/', $name))
                {
                    $context->local()->message(\Framework\Local::ERROR, 'You must provide a valid bean name');
                }
                else
                {
                    self::makebean($context, $name);
                    return TRUE;
                }
            }
            return FALSE;
        }
/**
 * Return the fields in this table
 *
 * @return array<string>
 */
        public function fields() : array
        {
            return \R::inspect($this->table);
        }
/**
 * Test if a field exists
 *
 * @param string   $fld The field name
 *
 * @return bool
 */
        public function hasField(string $fld) : bool
        {
            $flds = $this->fields();
            return isset($flds[$fld]);
        }
/**
 * Return the name of the table
 *
 * @return string
 */
        public function name() : string
        {
            return $this->table;
        }
/**
 * Setup for an edit
 *
 * @param \Support\Context    $context  The context object
 * @param array               $rest     The rest of the URL
 *
 * @return void
 */
        public function startEdit(Context $context, array $rest) : void
        {
            if (count($rest) >= 4)
            {
                try
                {
                    $bn = $context->load($rest[2], $rest[3]);
                    $context->local()->addval('object', $bn);
                }
                catch (\Framework\Exception\MissingBean $e)
                {
                    $context->local()->message(\Framework\Local::ERROR, 'Object does not exist');
                }
            }
        }
/**
 * Handle a bean edit
 *
 * @param \Support\Context    $context  The context object
 * @param array               $rest
 *
 * @return array
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function edit(Context $context, array $rest) : array
        {
            $emess = [];
            return [!empty($emess), $emess];
        }
/**
 * View a Bean
 *
 * @param \Support\Context    $context  The context object
 * @param array               $rest
 *
 * @return void
 */
        public function view(Context $context, array $rest) : void
        {
            $this->startEdit($context, $rest);
            $context->local()->addval('view', TRUE);
        }
    }
?>