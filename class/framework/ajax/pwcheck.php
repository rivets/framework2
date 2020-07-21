<?php
/**
pwcheck *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

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
        final public function handle() : void
        {
            /** @psalm-suppress PossiblyNullReference */
            if (($pw = $this->context->formdata('get')->fetch('pw', '')) === '' || !$this->context->user()->pwok($pw))
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
        }
    }
?>