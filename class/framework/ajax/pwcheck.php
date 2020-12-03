<?php
/**
 * Check password
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

/**
 * Operations on beans
 */
    class PwCheck extends Ajax
    {
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
            $this->context->web()->noContent();
        }
    }
?>