<?php
/**
 * A class that contains code to handle any /upload related requests.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2021 Newcastle University
 * @package Framework
 * @subpackage SystemPages
 */
    namespace Framework\Pages;

    use \Config\Config;
    use \Config\Framework as FW;
    use \Support\Context;
/**
 * Deal with a file upload URL
 */
    class Upload extends \Framework\SiteAction
    {
/**
 * Handle various admin operations /
 *
 * @param Context  $context    The context object for the site
 */
        public function handle(Context $context) : array|string
        {
            $fdt = $context->formdata('file');
            if ($fdt->hasForm())
            {
                if (Config::UPUBLIC && Config::UPRIVATE)
                { // need to check the flag could be either private or public
                    $fdp = $context->formdata('post');
                    foreach($fdt->fileArray('uploads') as $ix => $fa) // @phan-suppress-current-line PhanUndeclaredMethod
                    {
                        $upl = \R::dispense(FW::UPLOAD);
                        if (!$upl->savefile($context, $fa, $fdp->fetch(['public', $ix]), $context->user(), $ix))
                        { // something went wrong
                            $umodel = FW::UPLOADMCLASS;
                            $umodel::fail($context, $fa);
                        }
                        else
                        {
                            $context->local()->message(\Framework\Local::MESSAGE, $fa['name'].' uploaded');
                        }
                    }
                }
                else
                {
                    foreach($fdt->fileArray('uploads') as $ix => $fa) // @phan-suppress-current-line PhanUndeclaredMethod
                    { // we only support private or public in this case so there is no flag
                        $upl = \R::dispense(FW::UPLOAD);
                        if (!$upl->savefile($context, $fa, Config::UPUBLIC, $context->user(), $ix))
                        { // something went wrong
                            $umodel = FW::UPLOADMCLASS;
                            $umodel::fail($context, $fa);
                        }
                        else
                        {
                            $context->local()->message(\Framework\Local::MESSAGE, $fa['name'].' uploaded');
                        }
                    }
                }
            }
            return '@content/upload.twig';
        }
    }
?>