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
            ['get', ['exist', 0], '42', TRUE],
            ['get', ['notexist', 0], 0, FALSE],
            ['mustGet', ['exist', 0], '42', TRUE],
            ['mustGet', ['notexist', 0], '42', FALSE],
            ['get', [['aexist', 0], 0], '42', TRUE],
            ['get', [['aexist', 3], 0], 0, FALSE],
            ['must', [['aexist', 1], 0],'42', TRUE],
            ['mustGet', [['aexist', 3], 0], '42', FALSE],
            ['get', [['nexist', 14], 0], '42', TRUE],
            ['get', [['nexist', 13], 0], 0, FALSE],
            ['mustGet', [['nexist', 14], 0],'42', TRUE],
            ['mustGet', [['nexist', 13], 0], '42', FALSE],
            ['get', [['kexist', 'key1'], 0], '42', TRUE],
            ['get', [['kexist', 'key45'], 0], 0, FALSE],
            ['mustGet', [['kexist', 'key1'], 0],'42', TRUE],
            ['mustGet', [['kexist', 'key45'], 0], '42', FALSE],
            ['get', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
            ['mustGet', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
            ['get', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
            ['mustGet', ['email', FILTER_VALIDATE_INT], 3, TRUE],
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
            $tester = new \Framework\Support\TestSupport($context, 'GET');
            $test = $tester->run([
                ['hasget', ['exist'], TRUE, TRUE],
                ['hasget', ['notexist'], FALSE, FALSE],
                ['get', ['exist', 0], '42', TRUE],
                ['get', ['notexist', 0], 0, FALSE],
                ['mustget', ['exist', 0], '42', TRUE],
                ['mustget', ['notexist', 0], '42', FALSE],
                ['get', [['aexist', 0], 0], '42', TRUE],
                ['get', [['aexist', 3], 0], 0, FALSE],
                ['mustget', [['aexist', 1], 0],'42', TRUE],
                ['mustget', [['aexist', 3], 0], '42', FALSE],
                ['get', [['nexist', 14], 0], '42', TRUE],
                ['get', [['nexist', 13], 0], 0, FALSE],
                ['mustget', [['nexist', 14], 0],'42', TRUE],
                ['mustget', [['nexist', 13], 0], '42', FALSE],
                ['get', [['kexist', 'key1'], 0], '42', TRUE],
                ['get', [['kexist', 'key45'], 0], 0, FALSE],
                ['mustget', [['kexist', 'key1'], 0],'42', TRUE],
                ['mustget', [['kexist', 'key45'], 0], '42', FALSE],
                ['filterget', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
                ['mustfilterget', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
                ['filterget', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
                ['mustfilterget', ['email', FILTER_VALIDATE_INT], 3, FALSE],
            ]);
            $test = $tester->run(self::$tests, TRUE);
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
            $tester = new \Framework\Support\TestSupport($context, 'GET');
            $test = $tester->run([
                ['haspost', ['exist'], TRUE, TRUE],
                ['haspost', ['notexist'], FALSE, FALSE],
                ['post', ['exist', 3], '42', TRUE],
                ['post', ['notexist', 3], 3, FALSE],
                ['mustpost', ['exist', 3], '42', TRUE],
                ['mustpost', ['notexist', 3], '42', FALSE],
                ['post', [['aexist', 0], 3], '42', TRUE],
                ['post', [['aexist', 3], 3], 3, FALSE],
                ['mustpost', [['aexist', 3], 3], '42', FALSE],
                ['post', [['nexist', 14], 3], '42', TRUE],
                ['post', [['nexist', 13], 3], 3, FALSE],
                ['mustpost', [['nexist', 14], 3],'42', TRUE],
                ['mustpost', [['nexist', 13], 3], '42', FALSE],
                ['post', [['kexist', 'key1'], 3], '42', TRUE],
                ['post', [['kexist', 'key45'], 3], 3, FALSE],
                ['mustpost', [['kexist', 'key1'], 3],'42', TRUE],
                ['mustpost', [['kexist', 'key45'], 3], '42', FALSE],
                ['filterpost', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
                ['mustfilterpost', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
                ['filterpost', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
                ['mustfilterpost', ['email', FILTER_VALIDATE_INT], 3, FALSE],
            ]);
            $test = $tester->run(self::$tests, TRUE);
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
            $tester = new \Framework\Support\TestSupport($context, 'GET');
            $test = $tester->run([
                ['hasput', ['exist'], TRUE, TRUE],
                ['hasput', ['notexist'], FALSE, FALSE],
                ['put', ['exist', 3], '42', TRUE],
                ['put', ['notexist', 3], 3, FALSE],
                ['mustput', ['exist', 3], '42', TRUE],
                ['mustput', ['notexist', 3], '42', FALSE],
                ['put', [['aexist', 0], 3], '42', TRUE],
                ['put', [['aexist', 3], 3], 3, FALSE],
                ['mustput', [['aexist', 3], 3], '42', FALSE],
                ['put', [['nexist', 14], 3], '42', TRUE],
                ['put', [['nexist', 13], 3], 3, FALSE],
                ['mustput', [['nexist', 14], 3],'42', TRUE],
                ['mustput', [['nexist', 13], 3], '42', FALSE],
                ['put', [['kexist', 'key1'], 3], '42', TRUE],
                ['put', [['kexist', 'key45'], 3], 3, FALSE],
                ['mustput', [['kexist', 'key1'], 3],'42', TRUE],
                ['mustput', [['kexist', 'key45'], 3], '42', TRUE],
                ['filterput', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
                ['mustfilterput', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
                ['filterput', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
                ['mustfilterput', ['email', FILTER_VALIDATE_INT], 3, FALSE],
            ]);
            $test = $tester->run(self::$tests, TRUE);
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
            $tester = new \Framework\Support\TestSupport($context, 'GET');
            $test = $tester->run([
                ['hascookie', ['exist'], TRUE, TRUE],
                ['hascookie', ['notexist'], FALSE, FALSE],
                ['cookie', ['exist', 0], '42', TRUE],
                ['cookie', ['notexist', 0], 0, FALSE],
                ['mustcookie', ['exist', 0], '42', TRUE],
                ['mustcookie', ['notexist', 0], '42', FALSE],
                ['cookie', [['aexist', 0], 0], '42', TRUE],
                ['cookie', [['aexist', 3], 0], 0, FALSE],
                ['mustcookie', [['aexist', 3], 0], '42', FALSE],
                ['cookie', [['nexist', 14], 0], '42', TRUE],
                ['cookie', [['nexist', 13], 0], 0, FALSE],
                ['mustcookie', [['nexist', 14], 0],'42', TRUE],
                ['mustcookie', [['nexist', 13], 0], '42', FALSE],
                ['cookie', [['kexist', 'key1'], 0], '42', TRUE],
                ['cookie', [['kexist', 'key45'], 0], 0, FALSE],
                ['mustcookie', [['kexist', 'key1'], 0],'42', TRUE],
                ['mustcookie', [['kexist', 'key45'], 0], '42', FALSE],
                ['filtercookie', ['email', 'nobody@nowhere.com', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
                ['mustfiltercookie', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
                ['filtercookie', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
                ['mustfiltercookie', ['email', FILTER_VALIDATE_INT], 'foo@bar.com', FALSE],
            ]);
            $test = $tester->run(self::$tests, TRUE);
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