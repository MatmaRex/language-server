<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\Dispatcher;

use Amp\Success;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use stdClass;

class MethodDispatcherTest extends TestCase
{
    const EXPECTED_RESULT = 'Hello';

    private $argumentResolver;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var Handlers
     */
    private $handlers;

    protected function setUp(): void
    {
        $this->argumentResolver = $this->prophesize(ArgumentResolver::class);
        $this->handler = new class implements Handler {
            public function methods(): array
            {
                return [
                    'foobar' => 'foobar',
                ];
            }

            public function foobar(string $one, string $two)
            {
                return new Success(new stdClass());
            }
        };
    }

    public function testDispatchesRequest()
    {
        $dispatcher = $this->create();
        $handlers = new Handlers([
            $this->handler
        ]);
        $this->argumentResolver->resolveArguments($this->handler, 'foobar', [
            'one',
            'two'
        ])->willReturn([ 'one', 'two' ]);

        $response = \Amp\Promise\wait($dispatcher->dispatch($handlers, new RequestMessage(5, 'foobar', [ 'one', 'two' ]), []));

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertEquals(5, $response->id);
    }

    public function testAdditionalArgumentsPassedToResolver()
    {
        $dispatcher = $this->create();
        $handlers = new Handlers([
            $this->handler
        ]);

        $this->argumentResolver->resolveArguments($this->handler, 'foobar', [
            'one',
            'two',
            'three',
            'four',
        ])->willReturn([ 'one', 'two' ]);

        $response = \Amp\Promise\wait($dispatcher->dispatch($handlers, new RequestMessage(5, 'foobar', [ 'one', 'two' ]), [
            'three',
            'four',
        ]));

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertEquals(5, $response->id);
    }

    private function create(): MethodDispatcher
    {
        return new MethodDispatcher($this->argumentResolver->reveal());
    }
}
