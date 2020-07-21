<?php
/**
 * Base class for AJax operations
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Support\Context;
/**
 * Ajax operation base class
 */
    abstract class Ajax
    {
/**
 * @var Access
 */
        protected $access;
/**
 * Constructor
 */
        public function __construct()
        {
            $this->access = new Access();
            [$login, $permissions] = $this->requires();
            if ($login)
            { # this operation requires a logged in user
                $context->mustbeuser(); // will not return if there is no user
                /* NOT REACHED */
                try
                {
                    $this->access->checkPerms($context, $permissions);
                }
                catch (\Framework\Exception\Forbidden $e)
                {
                    throw $e;
                }
            }
        }
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [TRUE, []]; // default to requiring login
        }
/**
 * Handle AJAX operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return void
 */
        abstract public function handle(Context $context) : void;
    }
?>
    