<?php
/**
 * Contains definition of Test class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class Test
    {
        private static $tests = [ // function, parameters, expected result, if FALSE then failure is expected and result may be default or an exception
            ['exists', ['exist'], TRUE, TRUE],
            ['exists', ['notexist'], FALSE, TRUE],
            ['exists', [['aexist', 0]], TRUE, TRUE],
            ['exists', [['aexist', 3]], FALSE, TRUE],
            ['exists', [['nexist', 0]], FALSE, TRUE],
            ['mustExist', ['exist'], TRUE, TRUE],
            ['mustExist', ['notexist'], FALSE, FALSE],
            ['fetch', ['exist', 3], '42', TRUE],
            ['fetch', ['notexist', 3], 3, FALSE],
            ['mustFetch', ['notarray'], 3, FALSE],
            ['mustFetch', ['exist'], '42', TRUE],
            ['mustFetch', ['notexist'], '42', FALSE],
            ['fetch', [['aexist', 0], 3], '42', TRUE],
            ['fetch', [['aexist', 3], 3], 3, FALSE],
            ['fetch', ['aexist', 3, NULL, '', FALSE], 3, FALSE],
            ['fetch', ['aexist', 3, NULL, '', TRUE], ['42', '66'], TRUE],
            ['mustFetch', ['aexist', NULL, '', FALSE], 3, FALSE],
            ['mustFetch', ['aexist', NULL, '', TRUE], ['42', '66'], TRUE],
            ['mustFetch', [['aexist', 1]],'66', TRUE],
            ['mustFetch', [['aexist', 3]], '42', FALSE],
            ['fetch', [['nexist', 14], 3], '42', TRUE],
            ['fetch', [['nexist', 13], 3], 3, FALSE],
            ['mustFetch', [['nexist', 14]],'42', TRUE],
            ['mustFetch', [['nexist', 13]], '42', FALSE],
            ['fetch', [['kexist', 'key1'], 3], '42', TRUE],
            ['fetch', [['kexist', 'key45'], 3], 3, FALSE],
            ['mustFetch', [['kexist', 'key1']],'42', TRUE],
            ['mustFetch', [['kexist', 'key45']], '42', FALSE],
            ['fetch', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
            ['mustFetch', ['email', FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
            ['fetch', ['email', 3, FILTER_VALIDATE_INT], 3, FALSE],
            ['mustFetch', ['email', FILTER_VALIDATE_INT], 3, FALSE],
            ['mustFetchBean', ['beanid', 'user'], 'userid', TRUE],
            ['mustFetchBean', ['notbeanid', 'user'], 'userid', FALSE],
            ['mustFetchBean', ['badbeanid', 'user'], 'userid', FALSE],
            ['mustFetchBean', ['badbeanid2', 'user'], 'userid', FALSE],
            ['fetchArray', ['kexist'], ['iterator', ['key1' => 42, 'key2' => 43]], TRUE],
            ['mustFetchArray', ['kexist'], ['iterator', ['key1' => 42, 'key2' => 43]], TRUE],
            ['fetchArray', ['knotexist'], ['iterator', []], FALSE],
            ['mustFetchArray', ['knotexist'], ['iterator', []], FALSE],
        ];

        private static $oldtests = [ // function, parameters, expected result, if TRUE then failure is expected and result may be default or an exception
            ['has', ['exist'], TRUE, TRUE],
            ['has', ['notexist'], FALSE, FALSE],
            ['has', [['aexist', 0]], TRUE, TRUE],
            ['has', [['aexist', 3]], FALSE, TRUE],
            ['has', [['nexist', 0]], FALSE, TRUE],
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
            ['must', [['kexist', 'key45']], '42', FALSE],
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
 * mapping old tests
 *
 * @param string $type
 *
 * @return array
 */
        private static function mapping(string $type)
        {
            return array_map(static function ($item) use ($type) {
                return [$item[0].$type, $item[1], $item[2], $item[3]];
            }, self::$oldtests);
        }
/**
 * Do test
 *
 * @param string $type
 *
 * @return string
 */
        private static function dotest(Context $context, string $type) : string
        {
            $tester = new \Framework\Support\TestSupport($context, $type);
            $tester->run(self::mapping($type), TRUE);
            $tester->run(self::$tests, FALSE);
            $context->local()->addval('op', $type);
            if (filter_has_var(INPUT_GET, 'remote'))
            {
                $context->local()->addval('remote', TRUE);
            }
            return '@devel/tests/formdata.twig';
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
            return self::dotest($context, 'get');
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
            return self::dotest($context, 'post');
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
            return self::dotest($context, 'put');
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
            return self::dotest($context, 'cookie');
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
            // $tester = new \Framework\Support\TestSupport($context, 'file');
            $context->local()->addval('op', 'file');
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
            $fdt = $context->formdata('file');
            try
            {
                if ($fdt->exists('upload'))
                {
                    $upl = \R::dispense('upload');
                    $fa = $fdt->fileData('upload');
                    if (!$upl->savefile($context, $fa, FALSE, $context->user(), 0))
                    {
                        \Model\Upload::fail($context, $fa);
                    }
                    else
                    {
                        $context->local()->message(\Framework\Local::MESSAGE, $fa['name'].' uploaded');
                        $context->local()->addval('download', $upl->getID());
                    }
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
                    default:
                        throw new \Framework\Exception\BadValue('Illegal operation "'.$rest[2].'"');
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