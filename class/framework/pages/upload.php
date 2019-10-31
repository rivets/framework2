<?php
/**
 * A class that contains code to handle any /upload related requests.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2019 Newcastle University
 *
 */
    namespace Framework\Pages;

    use \Config\Config as Config;
    use \Support\Context as Context;
/**
 * Deal with a file upload URL
 */
    class Upload extends \Framework\SiteAction
    {
 /**
 * Handle various admin operations /upload
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            $fd = $context->formdata();
            if ($fd->hasfile('uploads'))
            {
                if (Config::UPUBLIC && Config::UPRIVATE)
                { # need to check the flag could be either private or public
                    foreach ($fd->posta('public') as $ix => $public)
                    {
                        $upl = \R::dispense('upload');
                        $upl->savefile($context, $fd->filedata('uploads', $ix), $public, $context->user(), $ix);
                    }
                }
                else
                {
                    foreach(new \Framework\FAIterator('uploads') as $ix => $fa)
                    { # we only support private or public in this case so there is no flag
                        $upl = \R::dispense('upload');
                        $upl->savefile($context, $fa, Config::UPUBLIC, $context->user(), $ix);
                    }
                }
            }
            return '@content/upload.twig';
        }
    }
?>
