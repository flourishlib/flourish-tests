<?php
require_once('./support/init.php');
 
class fUTF8Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		
	}
	
	public static function explodeProvider()
	{
		$output = array();
		
		$output[] = array('', NULL, array(''));
		$output[] = array(' ', NULL, array(' '));
		$output[] = array("a\nb", NULL, array("a", "\n", "b"));
		$output[] = array("\na\nb\n\n", NULL, array("\n", "a", "\n", "b", "\n", "\n"));
		$output[] = array('abcdefg', NULL, array('a', 'b', 'c', 'd', 'e', 'f', 'g'));
		$output[] = array('Iñtërnâtiônàlizætiøn', NULL, array('I', 'ñ', 't', 'ë', 'r', 'n', 'â', 't', 'i', 'ô', 'n', 'à', 'l', 'i', 'z', 'æ', 't', 'i', 'ø', 'n'));
		
		$output[] = array("a\nb", '', array("a", "\n", "b"));
		
		$output[] = array("a\nb", 'a', array("", "\nb"));
		
		$output[] = array("a\nb", "\n", array("a", "b"));
		$output[] = array('Iñtërnâtiônàlizætiøn', 'nà', array('Iñtërnâtiô', 'lizætiøn'));
		
		return $output;
	}
	
	/**
	 * @dataProvider explodeProvider
	 */
	public function testExplode($input, $delimiter, $output)
	{
		$this->assertEquals($output, fUTF8::explode($input, $delimiter));	
	}
	
	public static function rposProvider()
	{
		$output = array();
		
		$output[] = array('', '', 0, FALSE);
		$output[] = array(' ', '', 0, FALSE);
		$output[] = array('abc', '', 0, FALSE);
		
		$output[] = array("abc", 'a', 0, 0);
		$output[] = array("abc", 'b', 0, 1);
		$output[] = array("abc", 'c', 0, 2);
		$output[] = array("aaa", 'a', 0, 2);
		
		$output[] = array("aaa", 'a', 1, 2);
		$output[] = array("aaa", 'a', 2, 2);
		
		$output[] = array("aaa", 'a', -1, 2);
		$output[] = array("aaa", 'a', -2, 1);
		$output[] = array("aaa", 'a', -3, 0);
		
		$output[] = array('Iñtërnâtiônàlizætiøn', 'â', 0, 6);
		$output[] = array('Iñtërnâtiônàlizætiøn', 'æ', 0, 15);
		
		return $output;
	}
	
	/**
	 * @dataProvider rposProvider
	 */
	public function testRpos($input, $needle, $offset, $output)
	{
		$this->assertEquals($output, fUTF8::rpos($input, $needle, $offset));	
	}
	
	public function tearDown()
	{
		
	}
}