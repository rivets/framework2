<?php
/**
 * Contains definition of Admin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
    namespace Framework\Pages;

    use \Framework\Web\Web as Web;
/**
 * A class that contains code to handle any /admin related requests.
 *
 * Admin status is checked in index.php so does not need to be done here.
 */
    class Admin extends \Framework\Siteaction
    {
        const EDITABLE = ['form', 'page', 'user'];
        const VIEWABLE = ['form'];
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
                if (!in_array($kind, self::EDITABLE))
                {
                    $context->web()->bad();
                    /* not reached */
                }
                $obj = $context->load($kind, $rest[2]);
                if (($bid = $context->formdata()->post('bean', '')) !== '')
                { // this is a post
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

	    case 'view' : // view something - forms only at the moment
	        if (count($rest) < 3)
                {
                    $context->local()->bad();
                }
                $kind = $rest[1];
                if (!in_array($kind, self::VIEWABLE))
                {
                    $context->web()->bad();
                    /* NOT REACHED */
                }
                $obj = $context->load($kind, $rest[2]);
                $context->local()->addval($kind, $obj);
                $tpl = 'support/view'.$kind.'.twig';
                break;

	    case 'update':
                $ufd = fopen('https://catless.ncl.ac.uk/frameworknew/update/', 'r');
                if ($ufd)
                {
                    $upd = json_decode(fread($ufd, 1024));
                    if (isset($upd->config))
                    { // now see if there are any config values that need updating.
                        foreach ($upd->config as $cname => list($cvalue, $cfixed))
                        {
                            $lval = \R::findOne('fwconfig', 'name=?', [$cname]);
                            if (is_object($lval))
                            {
                                if (/*$lval->local == 0 && */$lval->value != $cvalue)
                                { // update if not locally set and there is a new value
                                    $lval->value = $cvalue;
                                    $lval->fixed = $cfixed;
                                    $lval->local = 0; // Once everyone has the local column this can go away
                                    $lval->fixed = $cfixed;
                                    \R::store($lval);
                                }
                            }
                            else
                            {
                                $lval = \R::dispense('fwconfig');
                                $lval->name = $cname;
                                $lval->value = $cvalue;
                                $lval->local = 0;
                                $lval->fixed = $cfixed;
                                \R::store($lval);
                            }
                        }
                    }
                    $context->local()->addval('version', $upd->version);
                    fclose($ufd);
                    $context->local()->addval('current', trim(file_get_contents($context->local()->makebasepath('version.txt'))));
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
