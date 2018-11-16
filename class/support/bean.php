<?php
/**
 * A class for the object Bean
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018 Newcastle University
 *
 */
    namespace Support;

    use \Support\Context as Context;
/**
 * A class Bean object
 */
    class Bean
    {
/**
 * @var string The name of the bean (i.e. table)
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
 * Return the fields
 *
 * @return array
 */
        public function fields()
        {
            return \R::inspect($this->table);
        }
/**
 * Return the name
 *
 * @return sting
 */
        public function name()
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
        public function startEdit(Context $context)
        {
        }
/**
 * Handle a bean edit
 *
 * @param object    $context  The context object
 *
 * @return void
 */
        public function edit(Context $context, array $rest)
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
            if (count($rest) >= 4)
            {
                $context->local()->addval('object', $context->load($rest[2], $rest[3]));
            }
            $context->local()->addval('view', TRUE);
        }
    }
?>
