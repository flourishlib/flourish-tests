<?php
require_once('./support/init.php');
include('./support/fMailbox.php');
 
class fEmailTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{	
		if (defined('SKIPPING') || !defined('EMAIL_PASSWORD')) {
			$this->markTestSkipped();
		}
		if (stripos(php_uname('s'), 'netbsd') !== FALSE || file_exists('/etc/SuSE-release')) {
			fEmail::fixQmail();
		}
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING') || !defined('EMAIL_PASSWORD')) {
			return;
		}
		
		if (file_exists('./output/fEmail_loaded_body.txt')) {
			unlink('./output/fEmail_loaded_body.txt');
		}
		if (file_exists('./output/fEmail_loaded_body.html')) {
			unlink('./output/fEmail_loaded_body.html');
		}
	}
	
	private function findMessage($token)
	{
		$mailbox = new fMailbox(EMAIL_SERVER, EMAIL_USER, EMAIL_PASSWORD);
		
		$i = 0;
		do {
			sleep(1);
			$messages = $mailbox->listMessages();
			foreach ($messages as $number => $headers) {
				if (strpos($headers['subject'], $token) !== FALSE) {
					$message = $mailbox->getMessage($number, TRUE);
					$mailbox->deleteMessage($number);
					return $message;
				}
			}
			$i++;
		} while ($i < 15);
		
		throw new Exception('Email message ' . $token . ' never arrived');
	}
	
	private function generateSubjectToken()
	{
		return uniqid('', TRUE);
	}	
		
	public function testSendSimple()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['Subject']);
		$this->assertEquals('This is a simple test', $message['plain']);
	}
	
	
	public function testSendSinglePeriodOnLine()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Single Periods on a Line');
		$email->setBody('This is a test of single periods on a line
.
.');
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Single Periods on a Line', $message['headers']['Subject']);
		$this->assertEquals('This is a test of single periods on a line
.
.', $message['plain']);
	}
	
	
	public function testSendFormattedBody()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Unindented Bodies');
		define('EMAIL_FORMATTED_BODY', 'set');
		$email->setBody('
			This is a test
			
			It uses the unindent and interpolate constants functionality that is available with fEmail::setBody()
			
			The constant is {EMAIL_FORMATTED_BODY}
			{EMAIL_BODY}
		', TRUE);
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Unindented Bodies', $message['headers']['Subject']);
		$this->assertEquals('This is a test

It uses the unindent and interpolate constants functionality that is available with fEmail::setBody()

The constant is set
{EMAIL_BODY}', $message['plain']);
	}
	
	
	public function testSendLoadedBody()
	{
		$token = $this->generateSubjectToken();
		
		file_put_contents(
			'./output/fEmail_loaded_body.txt',
			'This is a loaded body
With a couple of different $PLACEHOLDER$ styles, including dollar signs and %PERCENT_SIGNS%

You can replace nothing'
		);
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->loadBody('./output/fEmail_loaded_body.txt', array(
			'$PLACEHOLDER$' => 'placeholder',
			'%PERCENT_SIGNS%' => 'percent signs',
			'nothing' => 'anything'
		));
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['Subject']);
		$this->assertEquals('This is a loaded body
With a couple of different placeholder styles, including dollar signs and percent signs

You can replace anything', $message['plain']);
	}
	
	
	public function testSendLoadedHtml()
	{
		$token = $this->generateSubjectToken();
		
		file_put_contents(
			'./output/fEmail_loaded_body.html',
			'<h1>Loaded HTML</h1><p>%REPLACE%</p>'
		);
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a test of loading the HTML body');
		$email->loadHTMLBody(new fFile('./output/fEmail_loaded_body.html'), array('%REPLACE%' => 'This is a test'));
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['Subject']);
		$this->assertEquals('This is a test of loading the HTML body', $message['plain']);
		$this->assertEquals('<h1>Loaded HTML</h1><p>This is a test</p>', $message['html']);
	}
	
	
	public function testSendHtml()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		$email->setHTMLBody('<h1>Test</h1><p>This is a simple test</p>');
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Simple Email', $message['headers']['Subject']);
		$this->assertEquals('This is a simple test', $message['plain']);
		$this->assertEquals('<h1>Test</h1><p>This is a simple test</p>', $message['html']);
	}
	
	public function testSendLongSubject()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': This is a test of sending a long subject that should theoretically cause the email Subject: header to break onto multiple lines using folding whitespace - it should take less than 78 characters but it could be as long as 998 characters');
		$email->setBody('This is a simple test');
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': This is a test of sending a long subject that should theoretically cause the email Subject: header to break onto multiple lines using folding whitespace - it should take less than 78 characters but it could be as long as 998 characters', $message['headers']['Subject']);
		$this->assertEquals('This is a simple test', $message['plain']);
	}
	
	public function testSendUtf8()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com', "Wíll");
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': This is a test of sending headers and body with UTF-8, such as Iñtërnâtiônàlizætiøn');
		$email->setBody('This is a test with UTF-8 characters, such as:
Iñtërnâtiônàlizætiøn
');
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals((stripos(php_uname('s'), 'windows') !== FALSE) ? 'will@flourishlib.com' : 'Wíll <will@flourishlib.com>', $message['headers']['From']);
		$this->assertEquals($token . ': This is a test of sending headers and body with UTF-8, such as Iñtërnâtiônàlizætiøn', $message['headers']['Subject']);
		$this->assertEquals('This is a test with UTF-8 characters, such as:
Iñtërnâtiônàlizætiøn
', $message['plain']);
	}
	
	public function testSendAttachment()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Attachments');
		$email->setBody('This is a test of sending an attachment');
		$bar_gif_contents = file_get_contents('./resources/images/bar.gif');
		$email->addAttachment('bar.gif', 'image/gif', $bar_gif_contents);
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Attachments', $message['headers']['Subject']);
		$this->assertEquals('This is a test of sending an attachment', $message['plain']);
		$this->assertEquals(
			array(
				array(
					'filename' => 'bar.gif',
					'mimetype' => 'image/gif',
					'contents' => $bar_gif_contents
				)
			),
			$message['attachments'],
			'The attachment did not match the original file contents'
		);
	}
	
	public function testSendAttachments()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Attachments');
		$email->setBody('This is a test of sending an attachment');
		$bar_gif_contents = file_get_contents('./resources/images/bar.gif');
		$email->addAttachment('bar.gif', 'image/gif', $bar_gif_contents);
		$example_json_contents = '{
	"glossary": {
		"title": "example glossary",
		"GlossDiv": {
			"title": "S",
			"GlossList": {
				"GlossEntry": {
					"ID": "SGML",
					"SortAs": "SGML",
					"GlossTerm": "Standard Generalized Markup Language",
					"Acronym": "SGML",
					"Abbrev": "ISO 8879:1986",
					"GlossDef": {
						"para": "A meta-markup language, used to create markup languages such as DocBook.",
						"GlossSeeAlso": ["GML", "XML"]
					},
					"GlossSee": "markup"
				}
			}
		}
	}
}';
		$email->addAttachment('example.json', 'application/json', $example_json_contents);
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Attachments', $message['headers']['Subject']);
		$this->assertEquals('This is a test of sending an attachment', $message['plain']);
		$this->assertEquals(
			array(
				array(
					'filename' => 'bar.gif',
					'mimetype' => 'image/gif',
					'contents' => $bar_gif_contents
				),
				array(
					'filename' => 'example.json',
					'mimetype' => 'application/json',
					'contents' => $example_json_contents
				)
			),
			$message['attachments'],
			'The attachments did not match the original files\' contents'
		);
	}
	
	public function testSendHtmlAndAttachment()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->setSubject($token . ': Testing Attachment/HTML');
		$email->setBody('This is a test of sending an attachment with an HTML body');
		$email->setHTMLBody('<h1>Attachment/HTML Body Test</h1>
<p>
	This is a test of sending both an HTML alternative, while also sending an attachment.
</p>');
		$bar_gif_contents = file_get_contents('./resources/images/bar.gif');
		$email->addAttachment('bar.gif', 'image/gif', $bar_gif_contents);
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Attachment/HTML', $message['headers']['Subject']);
		$this->assertEquals('This is a test of sending an attachment with an HTML body', $message['plain']);
		$this->assertEquals('<h1>Attachment/HTML Body Test</h1>
<p>
	This is a test of sending both an HTML alternative, while also sending an attachment.
</p>', $message['html']);
		$this->assertEquals(
			array(
				array(
					'filename' => 'bar.gif',
					'mimetype' => 'image/gif',
					'contents' => $bar_gif_contents
				)
			),
			$message['attachments'],
			'The attachment did not match the original file contents'
		);
	}
	
	
	public function testSendPreventHeaderInjection()
	{
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(str_replace('@', "@\n", EMAIL_ADDRESS), "Test\nUser");
		$email->setSubject($token . ': Testing Header Injection');
		$email->setBody('This is a test of removing newlines from recipients and subject headers to help prevent email header injection');
		$email->send();
		
		$message = $this->findMessage($token);
		$this->assertEquals('will@flourishlib.com', $message['headers']['From']);
		$this->assertEquals($token . ': Testing Header Injection', $message['headers']['Subject']);
		$this->assertEquals('This is a test of removing newlines from recipients and subject headers to help prevent email header injection', $message['plain']);
	}
	
	
	public function testClearRecipients()
	{
		$this->setExpectedException('fValidationException');
		$token = $this->generateSubjectToken();
		
		$email = new fEmail();
		$email->setFromEmail('will@flourishlib.com');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User');
		$email->addRecipient(EMAIL_ADDRESS, 'Test User 2');
		$email->setSubject($token . ': Testing Simple Email');
		$email->setBody('This is a simple test');
		
		$email->clearRecipients();
		$email->send();
	}
}