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
    use \Support\Context as Context;

/**
 * A class that contains code to implement a contact page
 */
    class CSPReport extends \Framework\SiteAction
    {
/**
 * Handle various contact operations /contact
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            mail(Config::SYSADMIN, Config::SITENAME.' CSP Error Report',
                 file_get_contents('php://input'), // get the JSON ereport
                 'From: CSP Report <'.Config::SITENOREPLY.'>'.PHP_EOL);
            header(\Framework\Web\StatusCodes::httpHeaderFor(\Framework\Web\StatusCodes::HTTP_NO_CONTENT));
            exit;
//            return ['', '', \Framework\Web\StatusCodes::HTTP_NO_CONTENT];
        }
    }
?>
