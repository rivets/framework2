<?php
/**
 * Contains definition of Admin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2019 Newcastle University
 */
    namespace Framework\Pages;

    use \Framework\Web\Web as Web;
    use \Support\Context as Context;
    use \Config\Framework as FW;
/**
 * A class that contains code to handle any /admin related requests.
 *
 * Admin status is checked in index.php so does not need to be done here.
 */
    class Admin extends \Framework\SiteAction
    {
        const EDITABLE = [FW::TABLE, FW::FORM, FW::CONFIG, FW::PAGE, FW::USER];
        const VIEWABLE = [FW::TABLE, FW::FORM];
        const NOTMODEL = [FW::TABLE];
        const HASH     = 'sha384';
        
        use \Support\Nocache; // don't cache admin pages.
/**
 * Calculate integrity checksums for local js and css files
 *
 * @param \Support\Context   $context
 *
 * @return void
 */
        private function checksum(Context $context) : void
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
                        if ($base != '/' && $base !== '')
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
                        else
                        {
                            $fv = $fname;
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
 * @param \Support\Context    $context  The Context object
 * @param array     $rest     The rest of the URL
 *
 * @throws \Framework\Exception\Forbidden
 * @throws \Framework\Exception\ParameterCount
 *
 * @return string
 */
        private function edit(Context $context, array $rest) : string
        {
            if (count($rest) < 3)
            {
                throw new \Framework\Exception\ParameterCount('Too few parameters');
            }
            $kind = $rest[1];
            if (!in_array($kind, self::EDITABLE))
            {
                throw new \Framework\Exception\Forbidden('Not editable');
            }
            if (($notmodel = in_array($kind, self::NOTMODEL)))
            {
                $class = '\\Support\\'.$kind;
                try
                {
                    /** @psalm-suppress InvalidStringClass */
                    $obj = new $class($rest[2]);
                }
                catch (\Exception $e)
                {
                    $context->local()->message(\Framework\Local::ERROR, $e->getMessage());
                    $obj = NULL;
                }
            }
            else
            {
                $obj = $context->load($kind, $rest[2]);
            }
            $context->local()->addval('bean', $obj);
            if (is_object($obj))
            {
                $obj->startEdit($context, $rest); // do any special setup that the edit requires
                if (($bid = $context->formdata()->post('bean', '')) !== '')
                { // this is a post
                    if (($notmodel && $bid != $kind) || $bid != $obj->getID())
                    { # something odd...
                        throw new \Exception('Oddness');
                    }
                    \Framework\Utility\CSRFGuard::getinstance()->check();
                    try
                    {
                        list($error, $emess) = $obj->edit($context); // handle the edit result
                    }
                    catch (\Exception $e)
                    {
                        $error = TRUE;
                        $emess = $e->getMessage();
                    }
                    if ($error)
                    {
                        $context->local()->message(\Framework\Local::ERROR, $emess);
                    }
                    // The edit call might divert to somewhere else so sometimes we may not get here.
                    $context->local()->message(\Framework\Local::MESSAGE, 'Saved');
                }
            }
            return '@edit/'.$kind.'.twig';
        }
/**
 * View admin items
 *
 * @param \Support\Context    $context  The Context object
 * @param array     $rest     The rest of the URL
 *
 * @return string
 */
        private function view(Context $context, array $rest) : string
        {
            if (count($rest) < 3)
            {
                throw new \Framework\Exception\ParameterCount('Too few parameters');
            }
            $kind = $rest[1];
            if (!in_array($kind, self::VIEWABLE))
            {
                throw new \Framework\Exception\Forbidden('Not Viewable');
            }
            if (($notmodel = in_array($kind, self::NOTMODEL)))
            {
                $class = '\\Support\\'.$kind;
                try
                {
                    /** @psalm-suppress InvalidStringClass */
                    $obj = new $class($rest[2]);
                }
                catch (\Exception $e)
                {
                    $context->local()->message(\Framework\Local::ERROR, $e->getMessage());
                    $obj = NULL;
                }
            }
            else
            {
                $obj = $context->load($kind, $rest[2]);
            }
            if (is_object($obj))
            {
                $obj->view($context, $rest); // do any required set up
                $context->local()->addval('bean', $obj);
            }
            return '@view/'.$kind.'.twig';
        }
/**
 * Check for version updates and update config info
 *
 * @param \Support\Context    $context  The Context object
 *
 * @return string
 */
        private function update(Context $context) : string
        {
            $updated = [];
            $upd = json_decode(file_get_contents('https://catless.ncl.ac.uk/framework/update/'));
            if (isset($upd->fwconfig))
            { // now see if there are any config values that need updating.
                $base = $context->local()->base();
                foreach ($upd->fwconfig as $cname => $cdata)
                {
                    $lval = \R::findOne(FW::CONFIG, 'name=?', [$cname]);
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
                        $lval = \R::dispense(FW::CONFIG);
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
                if (isset($upd->message))
                { // there is a message about the update
                    $context->local()->message(\Framework\Local::MESSAGE, $upd->message);
                }
                $context->local()->addval([
                    'version'   => $upd->version,
                    'updated'   => $updated,
                    'current'   => trim(file_get_contents($context->local()->makebasepath('version.txt'))),
                ]);
            }
            return '@admin/update.twig';
        }
/**
 * Handle various admin operations /admin/xxxx
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            $rest = $context->rest();
            $context->setpages(); // most of hte pages use pagination so get values if any
            switch ($rest[0])
            {
            case 'beans':
                $tpl = '@admin/beans.twig';
                break;
            case 'checksum': // calculate checksums for locally included files
                $context->local()->message(\Framework\Local::WARNING, 'Currently not supported');
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