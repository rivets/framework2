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

    use Endroid\QrCode\Builder\Builder;
    use Endroid\QrCode\Encoding\Encoding;
    use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
    use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
    use Endroid\QrCode\Label\Font\NotoSans;
    use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
    use Endroid\QrCode\Writer\PngWriter;

    use \Support\Context;
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
                $context->local()->message(\Framework\Local::WARNING, '2-Factor Authentication disabled');
            }
            elseif ($user->secret() === '')
            { // enabling it
                $secret  = \Framework\Support\Security::getinstance()->make2FASecret();
                $user->code2fa = $secret;
                \R::store($user);
                $result = Builder::create()
                    ->writer(new PngWriter())
                    ->writerOptions([])
                    ->data('otpauth://totp/'.$user->login.'/?secret='.$secret)
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                    ->size(300)
                    ->margin(10)
                    ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                    ->labelText($context->local()->configVal('sitename'))
                    ->labelAlignment(new LabelAlignmentCenter())
                    ->build();
                $context->local()->addval([
                    'qrcode' => $result->getDataURI() //'data:image/png;base64,'.base64_encode($stringdata)
                ]);
            }

            return '@util/add2fa.twig';
        }
    }
?>