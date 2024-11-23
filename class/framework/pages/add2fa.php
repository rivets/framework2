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

    use \Endroid\QrCode\Builder\Builder;
    use \Endroid\QrCode\Encoding\Encoding;
    use \Endroid\QrCode\ErrorCorrectionLevel;
    use \Endroid\QrCode\RoundBlockSizeMode;
    use \Endroid\QrCode\Writer\PngWriter;
    use \Support\Context;
/**
 * An Add2FA page class
 */
    final class Add2FA extends \Framework\SiteAction
    {
/**
 * Handle various profile operations /
 * Returns a template name or an array with more complex information
 *
 * @param Context $context    The context object for the site
 */
        public function handle(Context $context) : string|array
        {
            $user = $context->user();
            $fdt = $context->formdata('post');
            if ($fdt->exists('validator'))
            {
                if (\Framework\Support\Security::getInstance()->check2FA($user->code2fa, $fdt->mustFetch('validator')))
                {
                    $user->secret = $user->code2fa;
                    $user->code2fa = '';
                    \R::store($user);
                    $context->local()->message(\Framework\Local::MESSAGE, '2-Factor Authentication enabled');
                }
                else
                {
                    $context->local()->message(\Framework\Local::ERROR, 'Please enter the code from your validator again');
                    $context->local()->addval('resend', TRUE);
                }
                return '@util/add2fa.twig';
            }
            if ($fdt->exists('disable'))
            {
                $user->secret = '';
                $user->code2fa = '';
                \R::store($user);
                $context->local()->addval('disabled', TRUE);
                $context->local()->message(\Framework\Local::WARNING, '2-Factor Authentication disabled');
            }
            elseif ($user->secret() === '')
            { // enabling it
                $secret  = \Framework\Support\Security::getinstance()->make2FASecret();
                $user->code2fa = $secret;
                \R::store($user);
//              $result = Builder::create()
//                  ->writer(new PngWriter())
//                  ->writerOptions([])
//                  ->data('otpauth://totp/'.\rawurlencode($context->local()->configVal('sitename').'  '.$user->login).'/?secret='.$secret)
//                  ->encoding(new Encoding('UTF-8'))
//                  ->errorCorrectionLevel(ErrorCorrectionLevel::High)
//                  ->size(300)
//                  ->margin(10)
//                  ->roundBlockSizeMode(\Endroid\QrCode\RoundBlockSizeMode::Margin)
//                  ->build();
                $builder = new Builder(
                    writer: new PngWriter(),
                    writerOptions: [],
                    data: 'otpauth://totp/'.\rawurlencode($context->local()->configVal('sitename').'  '.$user->login).'/?secret='.$secret,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 300,
                    margin: 10,
                    roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
                );
                $context->local()->addval('qrcode', $builder->build()->getDataURI());
            }

            return '@util/add2fa.twig';
        }
    }
?>