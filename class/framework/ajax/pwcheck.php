<?php
/**
pwcheck *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Support\Context;
/**
 * Operations on beans
 */
    class PwCheck extends Ajax
    {
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [FALSE, []]; // does not require login
        }
/**
 * Do a password verification
 *
 * @param Context    $context
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        final public function handle(Context $context) : void
        {
            /** @psalm-suppress PossiblyNullReference */
            if (($pw = $context->formdata('get')->fetch('pw', '')) === '' || !$context->user()->pwok($pw))
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
        }
    }
?>