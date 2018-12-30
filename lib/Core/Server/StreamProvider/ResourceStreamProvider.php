<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Psr\Log\LoggerInterface;

class ResourceStreamProvider implements StreamProvider
{
    /**
     * @var ResourceDuplexStream
     */
    private $duplexStream;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $provided = false;

    public function __construct(ResourceDuplexStream $duplexStream, LoggerInterface $logger)
    {
        $this->duplexStream = $duplexStream;
        $this->logger = $logger;
    }

    public function provide(): Promise
    {
        // resource connections are valid only for
        // the length of the client connnection
        if ($this->provided) {
            return new Success(null);
        }

        $this->provided = true;

        $this->logger->info('Listening on STDIO');
        return new Success($this->duplexStream);
    }
}