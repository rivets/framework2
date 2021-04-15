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
            if ($context->web()->isPost())
            {

                $fdt = $context->formdata('post');
                if ($fdt->exists('disable'))
                {
                    $user->secret = '';
                    \R::store($user);
                    $context->local()->message(\Framework\Local::WARNING, '2-Factor Authentication disabled');
                }
                elseif ($fdt->exists('validator'))
                {
                    if (\Framework\Support\Security::getInstance()->check2FA($user, $fdt->mustFetch('validator')))
                    {
                        $context->local()->message(\Framework\Local::MESSAGE, '2-Factor Authentication enabled');
                    }
                    else
                    {
                        $context->local()->message(\Framework\Local::ERROR, 'Please enter the code from your validator again');
                    }
                }
                else
                {
                    $context->divert('/');
                    /* NOT REACHED */
                }
            }
            if ($user->secret() === '')
            { // enabling it
                $secret  = \Framework\Support\Security::getinstance()->make2FASecret();
                $user->secret = $secret;
                \R::store($user);
                \ob_start();
                \QRcode::png('otpauth://totp/'.$context->local()->configVal('sitename').'/'.$user->login.'/?secret='.$secret, FALSE, QR_ECLEVEL_L, 6);
                $stringdata = \ob_get_clean();
                $context->local()->addval([
                    'qrcode' => 'data:image/png;base64,'.base64_encode($stringdata)
                ]);
            }
            return '@util/add2fa.twig';
        }
    }
?>