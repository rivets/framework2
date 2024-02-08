<?php
/**
 * Class to handle the Framework AJAX hints operation
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2020-2024 Newcastle University
 * @package Framework\Framework\Ajax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
    use \Framework\Exception\Forbidden;
/**
 * Get search hints for beans
 */
    class FWTest extends Ajax
    {
/**
 * @var array These permissions let the Site Admin manipulate the Framework internal tables. The first element is a
 *            bool indicating if a login is required, the second is a list of ['Context', 'Role'] pairs that a user
 *            must have. The third element is a list of accessible field names.
 */
        private static array $permissions = [
            FW::TEST => [ TRUE, [[FW::FWCONTEXT, FW::DEVELROLE]], ['f1'] ], // table does not always exist
        ];
/**
 * Return permission requirements
 *
 * First element is a bool indicating if login is required. The second element is a list of ['Context', 'Role']
 * that the user must have.
 */
        #[\Override]
        public function requires() : array
        {
            return [TRUE, [[FW::FWCONTEXT, FW::DEVELROLE]]]; // login not required
        }
/**
 * Get search hints for a bean
 *
 * @throws Forbidden
 */
        final public function handle() : void
        {
            $method = strtolower($this->context->web()->method());
            $res = (object) [
                'rest' => $this->context->rest(),
                'method' => $method,
                'data' => $this->context->formdata($method == 'patch' ? 'put' : $method)->fetchRaw(),
            ];
            $this->context->web()->sendJSON($res);
        }
    }
?>