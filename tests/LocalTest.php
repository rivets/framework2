<?php
    class LocalTest extends PHPUnit_Framework_TestCase
    {
	public function testMakepath()
	{
	    $local = Local::getinstance(); // Singleton class
	    $pn = $local->makepath('a', 'b', 'c');
	    $this->assertEquals($pn, 'a'.DIRECTORY_SEPARATOR.'b'.DIRECTORY_SEPARATOR.'c');
	}

	public function testEignore()
	{
	    $local = Local::getinstance();
	    $local->eignore(TRUE);
	    $x = $y / 0;
	    $local->assertTrue(TRUE);
    }
?>
