<?php
/**
 * A class for the object Table
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018 Newcastle University
 *
 */
    namespace Support;

    use \Support\Context as Context;
/**
 * A class Table object
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
 */
        public function __construct(string $name)
        {
            $this->table = $name;
        }
/**
 * add a new table
 */
        public static function add(Context $context) : bool
        {
            $fd = $context->formdata();
            if ($fd->haspost('name'))
            {
                $name = strtolower($fd->mustpost('name'));
                if ($name === '')
                {
                    $context->local()->essage(\Framework\Local::ERROR, 'You must provide a bean name');
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
                    \R::trash($bn); // delete it as we dont want it anymore
                    $context->local()->message(\Framework\Local::MESSAGE, $name.' created');
                    return TRUE;
                }
            }
            return FALSE;
        }
/**
 * Return the fields
 *
 * @return array
 */
        public function fields() : array
        {
            return \R::inspect($this->table);
        }
/**
 * Return the name
 *
 * @return sting
 */
        public function name() : string
        {
            return $this->table;
        }
/**
 * Setup for an edit
 *
 * @param object    $context  The context object
 *
 * @return void
 */
        public function startEdit(Context $context, array $rest)
        {
            if (count($rest) >= 4)
            {
                $bn = $context->load($rest[2], $rest[3], \Framework\Context::RNULL);
                if (is_object($bn))
                {
                    $context->local()->addval('object', $bn);
                }
                else
                {
                    $context->local()->message(\Framework\Local::ERROR, 'Object does not exist');
                }
            }
        }
/**
 * Handle a bean edit
 *
 * @param object    $context  The context object
 *
 * @return void
 */
        public function edit(Context $context, array $rest) : array
        {
            $emess = [];
            return [!empty($emess), $emess];
        }
/**
 * View a Bean
 *
 * @param object    $context  The context object
 *
 * @return void
 */
        public function view(Context $context, array $rest)
        {
            $this->startEdit($context, $rest);
            $context->local()->addval('view', TRUE);
        }
    }
?>
