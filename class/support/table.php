<?php
/**
 * A class for the object Table
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2019 Newcastle University
 *
 */
    namespace Support;

    use \Support\Context as Context;
/**
 * A class Table object
 */
    class Table
    {
/** @var string The name of the table */
        private $table;

        use \ModelExtend\MakeGuard;
/**
 * Constructor
 *
 * @param string $name The name of the table
 */
        public function __construct(string $name)
        {
            if (!\Support\Siteinfo::tableExists($name))
            {
                throw new \Framework\Exception\BadValue('Table does not exist');
                /* NOT REACHED */
            }
            $this->table = $name;
        }
/**
 * Add a new table
 *
 * @param \Support\Context    $context  The context object
 *
 * @return bool
 */
        public static function add(Context $context) : bool
        {
            $fd = $context->formdata();
            if ($fd->haspost('name'))
            {
                $name = strtolower($fd->mustpost('name'));
                if ($name === '')
                {
                    $context->local()->message(\Framework\Local::ERROR, 'You must provide a bean name');
                }
                elseif (!preg_match('/^[a-z][a-z0-9]*/', $name))
                {
                    $context->local()->message(\Framework\Local::ERROR, 'You must provide a bean name');
                }
                else
                {
                    $bn = \R::dispense(strtolower($name));
                    foreach ($fd->posta('field') as $ix => $field)
                    {
                        if ($field !== '')
                        {
                            if (!preg_match('/^[a-z][a-z0-9]*/', $field))
                            {
                                $context->local()->message(\Framework\Local::ERROR, 'Field names must be alphanumeric: '.$field.' not stored');
                            }
                            else
                            {
                                $bn->{$field} = $fd->post(['sample', $ix], '');
                            }
                        }
                    }
                    \R::store($bn);
                    \R::exec('truncate '.$name); // clean out the table
                    $context->local()->message(\Framework\Local::MESSAGE, $name.' created');
                    return TRUE;
                }
            }
            return FALSE;
        }
/**
 * Return the fields
 *
 * @return string[]
 */
        public function fields() : array
        {
            return \R::inspect($this->table);
        }
/**
 * Test if a field exists
 *
 * @param string $fld The field name
 *
 * @return bool
 */
        public function hasField($fld) : bool
        {
            $flds = $this->fields();
            return isset($flds[$fld]);
        }
/**
 * Return the name
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
 * @param array               $rest
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
