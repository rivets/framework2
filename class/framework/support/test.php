<?php
/**
 * Contains definition of Test class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Support;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class Test
    {    
        static private $tests = [
            ['exist', ['exist'], TRUE, TRUE],
            ['exist', ['notexist'], FALSE, FALSE],
            ['mustExist', ['exist'], TRUE, TRUE],
            ['mustExist', ['notexist'], FALSE, FALSE],
            ['get', ['exist', 3], '42', TRUE],
            ['get', ['notexist', 3], 3, FALSE],
            ['mustGet', ['exist'], '42', TRUE],
            ['mustGet', ['notexist'], '42', FALSE],
            ['get', [['aexist', 3], 3], '42', TRUE],
            ['get', [['aexist', 3], 3], 3, FALSE],
            ['mustGet', [['aexist', 1]],'66', TRUE],
            ['mustGet', [['aexist', 3]], '42', FALSE],
            ['get', [['nexist', 14], 3], '42', TRUE],
            ['get', [['nexist', 13], 3], 3, FALSE],
            ['mustGet', [['nexist', 14]],'42', TRUE],
            ['mustGet', [['nexist', 13]], '42', FALSE],
            ['get', [['kexist', 'key1'], 0], '42', TRUE],
            ['get', [['kexist', 'key45'], 0], 0, FALSE],
            ['mustGet', [['kexist', 'key1']],'42', TRUE],
            ['mustGet', [['kexist', 'key45']], '42', FALSE],
            ['get', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
            ['mustGet', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
            ['get', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
            ['mustGet', ['email', FILTER_VALIDATE_INT], 3, FALSE],
        ];

        private static $oldtests = [
            ['has', ['exist'], TRUE, TRUE],
            ['has', ['notexist'], FALSE, FALSE],
            ['', ['exist', 3], '42', TRUE],
            ['', ['notexist', 3], 3, FALSE],
            ['must', ['exist'], '42', TRUE],
            ['must', ['notexist'], '42', FALSE],
            ['', [['aexist', 0], 3], '42', TRUE],
            ['', [['aexist', 3], 3], 3, FALSE],
            ['must', [['aexist', 1]],'66', TRUE],
            ['must', [['aexist', 3]], '42', FALSE],
            ['', [['nexist', 14], 3], '42', TRUE],
            ['', [['nexist', 13], 3], 3, FALSE],
            ['must', [['nexist', 14]],'42', TRUE],
            ['must', [['nexist', 13]], '42', FALSE],
            ['', [['kexist', 'key1'], 3], '42', TRUE],
            ['', [['kexist', 'key45'], 3], 3, FALSE],
            ['must', [['kexist', 'key1']],'42', TRUE],
            ['must', [['kexist', 'key45']], '42', TRUE],
            ['filter', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
            ['mustfilter', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
            ['filter', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
            ['mustfilter', ['email', FILTER_VALIDATE_INT], 3, FALSE],
        ];
/**
 * Test AJAX functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function ajax(Context $context) : string
        {
            $context->local()->addval('bean', \R::findOrCreate(FW::TEST, ['f1' => 'a string', 'tog' => 1]));
            return '@devel/testajax.twig';
        }
/**
 * Test failed assertion handling
 *
 * @param Context $context  The site context object
 *
 * @return void
 */
        public function assert(Context $context) : string
        {
            assert(TRUE == FALSE);
            $context->local()->message(\Framework\Local::ERROR, 'Assertion test : this should not be reached');
            return '@devel/devel.twig';
        }
/**
 * Test run time error handling
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function fail(Context $context) : string
        {
            2 / 0;
            $context->local()->message(\Framework\Local::ERROR, 'Failure test : this should not be reached');
            return '@devel/devel.twig';
        }
/**
 * Test the FormData Get functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function get(Context $context) : string
        {
            $tester = new \Framework\Support\TestSupport($context, 'get');
            $tx = array_map(function($item){
                return [$item[0].'get', $item[1], $item[2], $item[3]];
            }, self::$oldtests);
            $test = $tester->run($tx, TRUE);
            $test = $tester->run(self::$tests, FALSE);
            $context->local()->addval('op', 'get');
            return '@devel/tests/formdata.twig';
        }
/**
 * Test the FormData Post functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function post(Context $context) : string
        {
            $tester = new \Framework\Support\TestSupport($context, 'post');
            $tx = array_map(function($item){
                return [$item[0].'post', $item[1], $item[2], $item[3]];
            }, self::$oldtests);
            $test = $tester->run($tx, TRUE);
            $test = $tester->run(self::$tests, FALSE);
            $context->local()->addval('op', 'post');
            return '@devel/tests/formdata.twig';
        }
/**
 * Test the FormData Put functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function put(Context $context) : string
        {
            $tester = new \Framework\Support\TestSupport($context, 'put');
            $tx = array_map(function($item){
                return [$item[0].'put', $item[1], $item[2], $item[3]];
            }, self::$oldtests);
            $test = $tester->run($tx, TRUE);
            $test = $tester->run(self::$tests, FALSE);
            $context->local()->addval('op', 'put');
            return '@devel/tests/formdata.twig';
        }
/**
 * Test the FormData Cookie functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function cookie(Context $context) : string
        {
            $tester = new \Framework\Support\TestSupport($context, 'cookie');
            $tx = array_map(function($item){
                return [$item[0].'cookie', $item[1], $item[2], $item[3]];
            }, self::$oldtests);
            $test = $tester->run($tx, TRUE);
            $test = $tester->run(self::$tests, FALSE);
            $context->local()->addval('op', 'cookie');
            return '@devel/tests/formdata.twig';
        }
/**
 * Test the FormData File functions
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function file(Context $context) : string
        {
            return '@devel/devel.twig';
        }

/**
 * Test mail
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function mail(Context $context) : string
        {
/**
 * @psalm-suppress PossiblyNullPropertyFetch
 * @psalm-suppress PossiblyNullArgument
 */
            $msg = $context->local()->sendmail([$context->user()->email], 'test', 'test');
            if ($msg === '')
            {
                $context->local()->message(\Framework\Local::MESSAGE, 'sent');
            }
            else
            {
                $context->local()->message(\Framework\Local::ERROR, $msg);
            }
            return '@devel/devel.twig';
        }
/**
 * Generate a test page. This tests various twig macros etc.
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function page(Context $context) : string
        {
            $context->local()->message(\Framework\Local::ERROR, 'Error 1');
            $context->local()->message(\Framework\Local::ERROR, 'Error 2');
            $context->local()->message(\Framework\Local::WARNING, 'Warning 1');
            $context->local()->message(\Framework\Local::WARNING, 'Warning 2');
            $context->local()->message(\Framework\Local::MESSAGE, 'Message 1');
            $context->local()->message(\Framework\Local::MESSAGE, 'Message 2');
            return '@devel/test.twig';
        }
/**
 * Throw an unhandled exception. Tests exception handling.
 *
 * @param Context $context  The site context object
 *
 * @throws \Exception
 * @return void
 */
        public function toss(Context $context) : string
        {
            throw new \Exception('Unhandled Exception Test');
            $context->local()->message(\Framework\Local::ERROR, 'Throw test : this should not be reached');
            return '@devel/test.twig';
        }
/**
 * Test the upload features
 *
 * @param Context $context  The site context object
 *
 * @return string
 */
        public function upload(Context $context) : string
        {
            $fd = $context->formdata();
            try
            {
                if ($fd->hasfile('upload'))
                {
                    $upl = \R::dispense('upload');
                    $upl->savefile($context, $fd->filedata('upload'), FALSE, $context->user(), 0);
                    $context->local()->addval('download', $upl->getID());
                }
                $rest = $context->rest();
                if (count($rest) == 4)
                {
                    $id = (int) $rest[3];
                    switch ($rest[2])
                    {
                    case 'get':
                        $context->local()->addval('download', $id);
                        break;

                    case'delete':
                        \R::trash($context->load('upload', $id));
                        $context->local()->message(\Framework\Local::MESSAGE, 'Deleted');
                        break;
                    }
                }
            }
            catch (\Exception $e)
            {
                $context->local()->message(\Framework\Local::ERROR, $e->getmessage());
            }
            return '@devel/testupload.twig';
        }
    }
?>