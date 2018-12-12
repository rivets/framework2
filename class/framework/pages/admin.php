<?php
/**
 * Contains definition of Admin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
    namespace Framework\Pages;

    use \Framework\Web\Web as Web;
    use \Support\Context as Context;
    use \Config\Config as Config;
/**
 * A class that contains code to handle any /admin related requests.
 *
 * Admin status is checked in index.php so does not need to be done here.
 */
    class Admin extends \Framework\Siteaction
    {
        const EDITABLE = ['table', 'form', 'fwconfig', 'page', 'user'];
        const VIEWABLE = ['table', 'form'];
        const NOTMODEL = ['table'];
        const HASH     = 'sha384';
/**
 * Calculate integrity checksums for local js and css files
 *
 * @param string    $citem    The config item to check and update
 *
 * @return string
 */
        private function checksum(Context $context)
        {
            chdir($context->local()->basedir()); // make sure we are in the root of the site
            $base = $context->local()->base();
            foreach ($context->local()->allconfig() as $fwc)
            {
                switch ($fwc->type)
                {
                case 'css':
                case 'js':
                    if (!preg_match('#^(//|htt)#', $fwc->value)) // this is a local file
                    {
                        $fname = $fwc->value;
                        if ($base != '/' || $base !== '')
                        { // if there are sub directories then we need to remove them as we are there already...
                            if (preg_match('#^'.$base.'(.*)#', $fname, $m))
                            {
                                $fname = $m[1];
                                $fv = '%BASE%'.$fname;
                            }
                            else
                            {
                                $context->local()->message(\Framework\Local::ERROR, 'Could not de-base '.$fname.' ('.$base.')');
                                break;
                            }
                        }
                        $hash = hash(self::HASH, file_get_contents('.'.$fname), TRUE);
                        $fwc->value = $fv;
                        $fwc->integrity = self::HASH.'-'.base64_encode($hash);
                        $fwc->crossorigin = 'anonymous';
                        \R::store($fwc);
                    }
                    break;
                }
            }
        }
/**
 * Edit admin items
 *
 * @param object    $context  The Context object
 * @param array     $rest     The rest of the URL
 *
 * @return string
 */
        private function edit(Context $context, array $rest)
        {
            try
            {
                if (count($rest) < 3)
                {
                    throw new \Exception('Too Few');
                }
                $kind = $rest[1];
                if (!in_array($kind, self::EDITABLE))
                {
                    throw new \Exception('Not Editable');
                }
                if (($notmodel = in_array($kind, self::NOTMODEL)))
                {
                    $class = '\\Support\\'.$kind;
                    $obj = new $class($rest[2]);
                }
                else
                {
                    $obj = $context->load($kind, $rest[2]);
                }
                $context->local()->addval('bean', $obj);
                $obj->startEdit($context, $rest); // do any special setup that the edit requires
                if (($bid = $context->formdata()->post('bean', '')) !== '')
                { // this is a post
                    if (($notmodel && $bid != $kind) || $bid != $obj->getID())
                    { # something odd...
                        throw new \Exception('Oddness');
                    }
                    \Framework\Utility\CSRFGuard::getinstance()->check();
                    list($error, $emess) = $obj->edit($context); // handle the edit result
                    if ($error)
                    {
                        $context->local()->message(\Framework\Local::ERROR, $emess);
                    }
                    // The edit call might divert to somewhere else so sometimes we may not get here.
                    $context->local()->message(\Framework\Local::MESSAGE, 'Saved');
                }
            }
            catch (\Exception $e)
            {
                $context->web()->bad($e->getmessage());
                /* NOT REACHED */
            }
            return '@edit/'.$kind.'.twig';
        }
/**
 * View admin items
 *
 * @param object    $context  The Context object
 * @param array     $rest     The rest of the URL
 *
 * @return string
 */
        private function view(Context $context, array $rest)
        {
            try
            {
                if (count($rest) < 3)
                {
                    throw new \Exception('Too few');
                }
                $kind = $rest[1];
                if (!in_array($kind, self::VIEWABLE))
                {
                    throw new \Exception('Not Viewable');
                }
            }
            catch (\Exception $e)
            {
                $context->web()->bad($e->getMessage());
                /* NOT REACHED */
            }
            if (($notmodel = in_array($kind, self::NOTMODEL)))
            {
                $class = '\\Support\\'.$kind;
                $obj = new $class($rest[2]);
            }
            else
            {
                $obj = $context->load($kind, $rest[2]);
            }
            $obj->view($context, $rest); // do any required set up
            $context->local()->addval('bean', $obj);
            return '@view/'.$kind.'.twig';
        }
/**
 * Check for version updates and update config info
 *
 * @param object    $context  The Context object
 *
 * @return string
 */
        private function update(Context $context)
        {
            $updated = [];
            $upd = json_decode(file_get_contents('https://catless.ncl.ac.uk/framework/update/'));
            if (isset($upd->fwconfig))
            { // now see if there are any config values that need updating.
                $base = $context->local()->base();
                foreach ($upd->fwconfig as $cname => $cdata)
                {
                    $lval = \R::findOne('fwconfig', 'name=?', [$cname]);
                    if (is_object($lval))
                    {
                        if ($lval->local == 0)
                        { // update if not locally set and there is a new value
                            $change = FALSE;
                            foreach ($cdata as $k => $v)
                            {
                                $v = preg_replace('/%BASE%/', $base, $v); // relocate to this base.
                                if ($lval->$k != $v)
                                {
                                    $lval->$k = $v;
                                    $change = TRUE;
                                }
                            }
                            if ($change)
                            {
                                \R::store($lval);
                                $updated[$cname] = $cdata->value;
                            }
                        }
                    }
                    else
                    {
                        $lval = \R::dispense('fwconfig');
                        $lval->name = $cname;
                        $lval->local = 0;
                        foreach ($cdata as $k => $v)
                        {
                            $lval->$k = preg_replace('/%BASE%/', $base, $v); // relocate to this base.
                        }
                        \R::store($lval);
                        $updated[$cname] = $cdata->value;
                    }
                }
                $context->local()->addval([
                    'version'   => $upd->version,
                    'updated'   => $updated,
                    'current'   => trim(file_get_contents($context->local()->makebasepath('version.txt')))
                ]);
            }
            else
            {
                $context->local()->addval('nozip', TRUE);
            }
            return '@admin/update.twig';
        }
/**
 * Handle various admin operations /admin/xxxx
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            $rest = $context->rest();
            switch ($rest[0])
            {
            case 'beans':
                \Support\Table::add($context);
                $tpl = '@admin/beans.twig';
                break;
            case 'checksum':
                $this->checksum($context);
                $context->local()->message(\Framework\Local::MESSAGE, 'Done');
                $tpl = '@admin/admin.twig';
                break;
            case 'config':  // show and add config items
                $tpl = '@admin/config.twig';
                break;
            case 'contexts': // show and add contexts
                $tpl = '@admin/contexts.twig';
                break;
            case 'edit' : // Edit something - forms, user, pages...
                $tpl = $this->edit($context, $rest);
                break;
            case 'forms': // show and add forms
                $tpl = '@admin/forms.twig';
                break;
            case 'info': // generate phpinfo page
                $_SERVER['PHP_AUTH_PW'] = '*************'; # hide the password in case it is showing.
                phpinfo();
                exit; // phpinfo display is all we need
            case 'pages':  // show and add pages
                $tpl = '@admin/pages.twig';
                break;
            case 'roles': // show and add roles
                $tpl = '@admin/roles.twig';
                break;
            case 'update': // See if we need an update
                $tpl = $this->update($context);
                break;
            case 'users': //show and add users
                $tpl = '@admin/users.twig';
                break;
            case 'view' : // view something - forms only at the moment
                $tpl = $this->view($context, $rest);
                break;
            default :
                $tpl = '@admin/admin.twig';
                break;
            }
            return $tpl;
        }
    }
?>