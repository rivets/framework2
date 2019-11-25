<?php
/**
 * Handle assets access if that is what is wanted
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2019 Newcastle University
 *
 */
    namespace Framework\Pages;

    use \Framework\Web\Web as Web;
    use \Support\Context as Context;

/**
 * Handle all the cacheing stuff and maybe return a file
 */
    class Assets extends \Framework\SiteAction
    {
/** @var string	The file name */
        private $file = '';
/** @var int	Last modified time for the file */
        private $mtime = 0;
/** @var array Mime type values */
        private static $mtypes = [
            ''      => 'text/plain',
            'css'	=> 'text/css',
            'js'	=> 'text/javascript',
            'png'	=> 'image/png',
            'jpg'	=> 'image/jpeg',
            'jpeg'	=> 'image/jpeg',
            'gif'	=> 'image/gif',
            'ico'	=> 'image/x-icon',
        ];
/**
 * Handle access to things in assets
 *
 * You can rely on Apache to deal with these things normally, however if
 * you really want to get cacheability to be complete you either have to go through hoops
 * in the Apache config or you code it in here!
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            chdir($context->local()->assetsdir());
    
            $rest = $context->rest();
            $this->file = implode(DIRECTORY_SEPARATOR, $rest);
            $this->mtime = filemtime($this->file);
/**
 * PHP file info does not give the correct mime type for compressed css files
 * so we need to do it ourselves which is a pain
 */
            $fname = array_pop($rest);
            /** @psalm-suppress PossiblyFalseArgument */
            $dotp = strrchr($fname, '.');
            if ($dotp !== FALSE)
            {
                $ext = strtolower(substr($dotp, 1));
            }
            else
            {
                $ext = '';
            }
            if (isset(self::$mtypes[$ext]))
            {
                $mime = self::$mtypes[$ext];
            }
            else
            {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (($mime = finfo_file($finfo, $this->file)) === FALSE)
                { # there was an error of some kind.
                    $mime = '';
                }
                finfo_close($finfo);
            }
            $mag = $this->makemaxage($context);
            $web = $context->web();
            $web->addheader([
//              'Last-Modified'	=> $this->makemod($this->mtime),
                'Etag'		=> '"'.$this->makeetag($context).'"',
                'Expires'	=> $this->makemod(time()+$mag),
                'Cache-Control'	=> 'max-age='.$mag.',stale-while-revalidate=86400,stale-if-error=259200',
            ]);
            $this->ifmodcheck();
            $web->sendfile($this->file, $fname, $mime);
            return '';
        }
/**
 * Make an etag - overrides the function in SiteAction
 *
 * @param \Support\Context    $context   The context object for the site
 *
 * @return string
 */
        public function makeetag(Context $context) : string
        {
            return sprintf("%u", crc32($this->file)).'-'.$this->mtime.'-'.($context->web()->acceptgzip() ? 1 : 0);
        }
/**
 * Check an etag to see if we need to send the page again or not.
 *
 * @param \Support\Context    $context   The context object for the site
 * @param string	$tag	The etag value to check
 *
 * @return bool
 */
        public function checketag(Context $context, string $tag) : bool
        {
            return substr($tag, 0, -1) == substr($this->makeetag($context), 0, -1);
        }
/**
 * Make a maximum age - overrides function in SiteAction
 *
 * An hour for the most recent volume and a year for everything else
 *
 * @param \Support\Context    $context   The context object for the site
 *
 * @return integer
 */
        public function makemaxage(Context $context) : int
        {
            return 3600*24*365; # make it a year
        }
/**
 * Check a timestamp to see if we need to send the page again or not - overriding method in SiteAction
 *
 * @param \Support\Context    $context   The context object for the site
 * @param string	$time	The time value to check
 *
 * @return boolean
 */
        public function checkmodtime(Context $context, string $time) : bool
        {
            return $this->mtime > $time;
        }
    }
?>
