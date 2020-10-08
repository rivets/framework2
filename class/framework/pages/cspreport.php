<?php
/**
 * Class for handling csp error report messages
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2020 Newcastle University
 * @package Framework
 * @subpackage SystemPages
 */
    namespace Framework\Pages;

    use \Config\Config;
    use \Support\Context;
/**
 * A class that contains code to implement a contact page
 */
    class CSPReport extends \Framework\SiteAction
    {
/**
 * Handle various contact operations /contact
 *
 * @param Context  $context    The context object for the site
 *
 * @return string   A template name
 */
        public function handle(Context $context)
        {
            $context->local()->sendmail([Config::SYSADMIN], Config::SITENAME.' CSP Error Report',
                 file_get_contents('php://input'), // get the JSON report
                 '', ['From' => 'CSP Report <'.Config::SITENOREPLY.'>']);
            header(\Framework\Web\StatusCodes::httpHeaderFor(\Framework\Web\StatusCodes::HTTP_NO_CONTENT));
            exit;
//            return ['', '', \Framework\Web\StatusCodes::HTTP_NO_CONTENT];
        }
    }
?>
