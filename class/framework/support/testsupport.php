<?php
/**
 * Contains definition of Test class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Support;

    use \Framework\Local;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class TestSupport
    {
        private $local;
        private $fdt;
        private $noform = FALSE;
        
        public function __construct(Context $context, string $type)
        {
            $this->local = $context->local();
            $this->fdt = $context->formdata($type);
            $this->noform = $context->web()->method() == 'GET' && !isset($_GET['exist']) && !isset($_GET['cookie']);
        }
        
        private function display($pars)
        {
            $res = [];
            foreach ($pars as $v)
            {
                $xp = var_export($v, TRUE);
                if (preg_match('/^\s*array\s*\(\s*(.*\)\s*$/ims', $xp, $m))
                {
                    $res = '['.preg_replace('/\s+/ims', ', ', trim($m[1])).']';
                }
                else
                {
                    $res[] = $xp;
                }
            }
            return implode(', ', $res);
        }
/**
 * OK if true
 */
        private function test(string $func, array $params, $result, bool$throwOK) : bool
        {
            if ($this->noform)
            {
                return TRUE;
            }
            $this->local->addval('array', var_export($_REQUEST, TRUE));
            $msg = $func.'('.$this->display($params).')';
            try
            {
                $res = $this->fdt->{$func}(...$params);
                if ($res === $result)
                {
                    $this->local->message(Local::MESSAGE, $msg.' OK : expected '.var_export($result, TRUE).' got '.var_export($res, TRUE));
                    return TRUE;
                }
                else
                {
                    $this->local->message(Local::ERROR, $msg.' expected '.var_export($result, TRUE).' got '.var_export($res, TRUE));
                }
            }
            catch (\Framework\Exception\BadValue $e)
            {
                $this->local->message($throwOK ? Local::MESSAGE : Local::ERROR, $msg.' throws exception: '.get_class($e).' '.$e->getMessage());
                return $throwOK;
            }
            catch (\Exception $e)
            {
                $this->local->message(Local::ERROR, $msg.' throws exception: '.get_class($e).' '.$e->getMessage());
            }
            return FALSE;
        }
/**
 * Run tests
 *
 * @return void
 */
        public function run(array $tests)
        {
            foreach ($tests as $test)
            {
                [$func, $params, $result, $ok] = $test;
                $this->test($func, $params, $result, !$ok);
            }
        }
    }
?>