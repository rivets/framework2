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
                $tpl = '@admin/pages.twig';
                break;

            case 'contexts':
                $tpl = '@admin/contexts.twig';
                break;

            case 'roles':
                $tpl = '@admin/roles.twig';
                break;

            case 'users':
                $tpl = '@admin/users.twig';
                break;

            case 'forms':
                $tpl = '@admin/forms.twig';
                break;

            case 'config':
                $tpl = '@admin/config.twig';
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
                $tpl = '@edit/'.$kind.'.twig';
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
                $tpl = '@view/'.$kind.'.twig';
                break;

            case 'update':
                $ufd = fopen('https://catless.ncl.ac.uk/frameworknew/update/', 'r');
                if ($ufd)
                {
                    $upd = json_decode(fread($ufd, 1024));
                    if (isset($upd->config))
                    { // now see if there are any config values that need updating.
                        foreach ($upd->config as $cname => $cvalue)
                        {
                            $lval = \R::findOne('fwconfig', 'name=?', [$cname]);
                            if (is_object($lval))
                            {
                                if ($lval->local == 0 && $lval->value != $cvalue)
                                { // update if not locally set and there is a new value
                                    $lval->value = $cvalue;
                                    \R::store($lval);
                                }
                            }
                            else
                            {
                                $lval = \R::dispense('fwconfig');
                                $lval->name = $cname;
                                $lval->value = $cvalue;
                                $lval->local = 0;
                                \R::store($lval);
                            }
                        }
                    }
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
                $tpl = '@admin/update.twig';
                break;

            default :
                $tpl = '@admin/admin.twig';
                break;
            }
            return $tpl;
        }
    }
?>
