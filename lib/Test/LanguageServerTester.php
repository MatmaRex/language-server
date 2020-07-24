<?php

namespace Phpactor\LanguageServer\Test;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\DidOpenTextDocumentNotification;
use Phpactor\LanguageServerProtocol\DidOpenTextDocumentParams;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageSerializer;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use function Amp\Promise\wait;

final class LanguageServerTester
{
    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var MessageSerializer
     */
    private $messageSerializer;

    public function __construct(DispatcherFactory $factory, ClientCapabilities $capabilities)
    {
        $this->transmitter = new TestMessageTransmitter();
        $this->dispatcher = $factory->create($this->transmitter, new InitializeParams($capabilities));
        $this->messageSerializer = new MessageSerializer();
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $message): Promise
    {
        return $this->dispatcher->dispatch($message);
    }

    public function dispatchAndWait(RequestMessage $message): ?ResponseMessage
    {
        return wait($this->dispatcher->dispatch($message));
    }

    /**
     * @param array|object $params
     * @return Promise<ResponseMessage|null>
     */
    public function request(string $method, $params): Promise
    {
        $requestMessage = new RequestMessage(uniqid(), $method, $this->normalizeParams($params));

        return $this->dispatch($requestMessage);
    }

    /**
     * @param array|object $params
     */
    public function requestAndWait(string $method, $params): ?ResponseMessage
    {
        return wait($this->request($method, $this->normalizeParams($params)));
    }

    /**
     * @param array|object $params
     * @return Promise<ResponseMessage|null>
     */
    public function notify(string $method, $params): Promise
    {
        $notifyMessage = new NotificationMessage($method, $this->normalizeParams($params));

        return $this->dispatch($notifyMessage);
    }

    /**
     * @param array|object $params
     */
    public function notifyAndWait(string $method, $params): void
    {
        wait($this->notify($method, $this->normalizeParams($params)));
    }

    public function transmitter(): TestMessageTransmitter
    {
        return $this->transmitter;
    }

    public function openTextDocument(string $url, string $content): void
    {
        $this->notifyAndWait(DidOpenTextDocumentNotification::METHOD, new DidOpenTextDocumentParams(
            ProtocolFactory::textDocumentItem($url, $content)
        ));
    }

    /**
     * @param array|object $params
     * @return array<string,mixed>
     */
    private function normalizeParams($params): array
    {
        if (is_array($params)) {
            return $params;
        }

        return $this->messageSerializer->normalize($params);
    }
}