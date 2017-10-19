<?php
    namespace Framework\Pages;

    use \Framework\Web\Web as Web;

/**
 * Contains definition of Admin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class that contains code to handle any /admin related requests.
 *
 * Admin status is checked in index.php so does not need to be done here.
 */
    class Admin extends \Framework\Siteaction
    {
/**
 * Handle various admin operations /admin/xxxx
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    $rest = $context->rest();
	    switch ($rest[0])
	    {
	    case 'pages':
		$tpl = 'support/pages.twig';
		break;

	    case 'contexts':
		$tpl = 'support/contexts.twig';
		break;

	    case 'roles':
		$tpl = 'support/roles.twig';
		break;

	    case 'users':
		$tpl = 'support/users.twig';
		break;

	    case 'forms':
		$tpl = 'support/forms.twig';
		break;

	    case 'config':
		$tpl = 'support/config.twig';
		break;

	    case 'info':
		$_SERVER['PHP_AUTH_PW'] = '*************'; # hide the password in case it is showing.
	        phpinfo();
		exit;

	    case 'edit' : // Edit something - forms, user, pages...
	        if (count($rest) < 3)
            {
                $context->web()->bad();
                /* NOT REACHED */
            }
	        $kind = $rest[1];
            $obj = $context->load($kind, $rest[2]);
            if (!is_object($obj))
            {
                $context->web()->bad();
                /* NOT REACHED */
            }
            if (($bid = $context->formdata()->post('bean', '')) !== '')
            { # this is a post
                if ($bid != $obj->getID())
                { # something odd...
                    $context->web()->bad();
                    /* NOT REACHED */
                }
                $obj->edit($context);
                // The edit call might divert to somewhere else so sometimes we may not get here.
            }
            $context->local()->addval('bean', $obj);
            $tpl = 'support/edit'.$kind.'.twig';
            break;

	    case 'view' : // view something - forms
	        if (count($rest) < 3)
            {
                $context->local()->bad();
            }
	        $kind = $rest[1];
            $obj = $context->load($kind, $rest[2]);
            if (!is_object($obj))
            {
                $context->local()->bad();
            }
            if (($bid = $context->formdata()->post('bean', '')) !== '')
            { # this is a post
                if ($bid != $obj->getID())
                { # something odd...
                    $context->local()->bad();
                }
                $obj->edit($context);
                // The edit call might divert to somewhere else so sometimes we may not get here.
            }
            $context->local()->addval($kind, $obj);
            $tpl = 'support/view'.$kind.'.twig';
            break;

	    case 'update':
            $ufd = fopen('https://catless.ncl.ac.uk/frameworknew/update/', 'r');
            if ($ufd)
            {
                $upd = json_decode(fread($ufd, 1024));
                $context->local()->addval('version', $upd->version);
                fclose($ufd);
                $context->local()->addval('current', trim(file_get_contents($context->local()->makebasepath('version.txt'))));
            }
            elseif (function_exists('zip_open'))
            {
                $formd = $context->formdata();
                if ($formd->hasfile('update'))
                {
                    $data = $formd->filedata('update');
                    if (($zd = zip_open($data['tmp_name'])) === FALSE)
                    {
                        $context->local()->message(Local::ERROR, 'Cannot open the file');
                    }
                    else
                    {
                        $context->local()->message(Local::MESSAGE, 'Done');
                    }
                }
            }
            else
            {
                $context->local()->addval('nozip', TRUE);
            }
            $tpl = 'support/update.twig';
            break;

	    default :
            $tpl = 'support/admin.twig';
            break;
	    }
	    return $tpl;
	}
    }
?>
