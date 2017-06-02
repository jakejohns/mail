<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Protocol\AppendCrlfConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;

final class Client
{
    /**
     *
     */
    public CONST AUTH_NONE = 0;
    /**
     *
     */
    public CONST AUTH_PLAIN = 1;
    /**
     *
     */
    public CONST AUTH_LOGIN = 2;
    /**
     *
     */
    public CONST AUTH_AUTO = 3;
    /**
     * @var ConnectionInterface
     */
    private $connection;
    /**
     * @var NegotiationInterface[]
     */
    private $negotiators = [];

    /**
     * Client constructor.
     * @param ConnectionInterface $connection
     * @param iterable $negotiators
     */
    public function __construct(ConnectionInterface $connection, iterable $negotiators = [])
    {
        $this->connection = new AppendCrlfConnection($connection);

        foreach ($negotiators as $negotiator) {
            $this->addNegotiator($negotiator);
        }

        $this->connection->addListener('connect', function () {
            foreach ($this->negotiators as $negotiator) {
                $negotiator->negotiate($this);
            }
        });
    }

    /**
     * @param NegotiationInterface $negotiation
     */
    private function addNegotiator(NegotiationInterface $negotiation)
    {
        $this->negotiators[] = $negotiation;
    }

    /**
     * @param RequestInterface $command
     * @return Reply
     */
    public function request(RequestInterface $command): Reply
    {
        $command->execute($this->connection);

        $reply = new Reply($this);
        do {
            $line = $this->connection->receive();
            list($code, $more, $message) = preg_split('/([\s-]+)/', $line,2,PREG_SPLIT_DELIM_CAPTURE);
            $reply = $reply->withLine((int)$code, trim($message));
        } while (strpos($more, '-') === 0);

        return $reply;
    }
}