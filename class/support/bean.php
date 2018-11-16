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
        private $bean;

        use \ModelExtend\MakeGuard;
/**
 * Constructor
 */
        public function __construct(string $name)
        {
            $this->bean = $name;
        }
/**
 * Return the fields
 *
 * @return array
 */
        public function fields()
        {
            return \R::inspect($this->bean);
        }
/**
 * Return the name
 *
 * @return sting
 */
        public function name()
        {
            return $this->bean;
        }
/**
 * Handle a bean edit
 *
 * @param object    $context  The context object
 *
 * @return void
 */
        public function edit(Context $context)
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
        public function view(Context $context)
        {
        }
    }
?>
