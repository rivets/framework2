<?php
 /**
  * Class for handling csp error report messages
  *
  * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
  * @copyright 2018 Newcastle University
  */
    namespace Framework\Pages;

    use \Config\Config as Config;
    use \Framework\Local as Local;
    use \Framework\Context as Context;

/**
 * A class that contains code to implement a contact page
 */
    class CSPReport extends \Framework\Siteaction
    {
/**
 * Handle various contact operations /contact
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            $data = file_get_contents('php://input');  // get the JSON ereport
            mail(Config::SYSADMIN, 'CSP Error Report', $data);
            return ['', '', \Framework\Web\StatusCodes::HTTP_NO_CONTENT];
        }
    }
?>
