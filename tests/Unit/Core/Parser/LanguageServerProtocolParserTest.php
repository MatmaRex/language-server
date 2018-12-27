<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Parser;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Transport\Request;

class LanguageServerProtocolParserTest extends TestCase
{
    /**
     * @var LanguageServerProtocolParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new LanguageServerProtocolParser();
    }

    public function testFeed()
    {
        $payload = <<<EOT
 Content-Length: 80\r\n
 Content-Type: foo\r\n\r\n
 {
    "jsonrpc": "2.0",
    "id": 1,
    "method": "test",
    "params": {}
 }
EOT;
        $this->parser->on(LanguageServerProtocolParser::EVENT_REQUEST_READY, function (Request $request) {
            $this->assertEquals([
                'Content-Length' => '80',
                'Content-Type' => 'foo',
            ], $request->headers());

            $this->assertEquals([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'test',
                'params' => [],
            ], $request->body());
        });
        $this->parser->feed($payload);
        $this->assertEquals(2, $this->getCount());
    }
}
