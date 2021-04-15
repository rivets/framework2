<?php
/**
 * Add2FA page class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2021 Newcastle University
 * @package Framework
 * @subpackage Pages
 */
    namespace Framework\Pages;

    use \Support\Context;

    include __DIR__.'/../utility/phpqrcode.php';
/**
 * An Add2FA page class
 */
    class Add2FA extends \Framework\SiteAction
    {
/**
 * Handle various profile operations /
 *
 * @param Context $context    The context object for the site
 *
 * @return string|array     A template name or an array with more complex information
 */
        public function handle(Context $context)
        {
            $user = $context->user();
            //if ($user->secret() !== '')
            //{
            //    if ($context->web()->isPost())
            //    {
            //    }
            //}
            //else
            //{ // enabling it
                $secret  = (new \Framework\Utility\RandomStringGenerator())->generate(16);
                $user->secret = $secret;
                \R::store($user);
                \ob_start();
                \QRcode::png('otpauth://totp/test?secret='.$secret.'&issuer=catless.ncl.ac.uk');
                $stringdata = \ob_get_clean();
                $context->local()->addval([
                    'qrcode' => 'data:image/png;base64,'.base64_encode($stringdata)
                ]);
//            }
            return '@util/add2fa.twig';
        }
    }
?>