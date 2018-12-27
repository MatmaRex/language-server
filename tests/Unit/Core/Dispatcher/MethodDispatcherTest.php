<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;
use stdClass;

class MethodDispatcherTest extends TestCase
{
    const EXPECTED_RESULT = 'Hello';

    private $argumentResolver;

    public function setUp()
    {
        $this->argumentResolver = $this->prophesize(ArgumentResolver::class);
        $this->handler = $this->prophesize(Handler::class);
    }

    public function testDispatchesRequest()
    {
        $this->handler->name()->willReturn('foobar');
        $handlers = new Handlers([ $this->handler->reveal() ]);

        $dispatcher = $this->create([
            $this->handler->reveal()
        ]);
        $this->argumentResolver->resolveArguments($this->handler->reveal(), '__invoke', [
            'one',
            'two'
        ])->willReturn([ 'one', 'two' ]);

        $expectedResult = new stdClass();
        $this->handler->__invoke('one', 'two')->will(function () use ($expectedResult) {
            yield $expectedResult;
        });

        $messages = $dispatcher->dispatch($handlers, new RequestMessage(5, 'foobar', [ 'one', 'two' ]));

        $this->assertInstanceOf(Generator::class, $messages);
        $response = $messages->current();
        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertEquals($expectedResult, $response->result);
        $this->assertEquals(5, $response->id);
    }

    private function create(array $array): MethodDispatcher
    {
        return new MethodDispatcher($this->argumentResolver->reveal());
    }
}
