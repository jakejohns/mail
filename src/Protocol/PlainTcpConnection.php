<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

final class PlainTcpConnection extends AbstractConnection
{
    /**
     *
     */
    private const UPGRADE_TO = [
        STREAM_CRYPTO_METHOD_TLS_CLIENT => true,
        STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT => true,
        STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT => true,
        STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT => true,
    ];

    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var float
     */
    private $connectionTimeout;

    /**
     * PlainTcpConnection constructor.
     * @param string $host
     * @param int $port
     * @param float $connectionTimeout
     */
    public function __construct(string $host, int $port, float $connectionTimeout = 1)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @param int $type
     * @return ConnectionInterface
     */
    public function upgrade(int $type): ConnectionInterface
    {
        if (!isset(self::UPGRADE_TO[$type])) {
            throw new \InvalidArgumentException('No support for requested encryption type');
        }

        $resource = stream_socket_enable_crypto($this->resource, true, $type);
        if ($resource === false) {
            throw new \InvalidArgumentException('Cannot upgrade connection to requested encryption type');
        }

        return TlsConnection::fromResource($resource, $this->host, $this->port);
    }

    /**
     *
     */
    protected function connect()
    {
        if (is_resource($this->resource)) {
            return;
        }

        $this->resource = @stream_socket_client(
            'tcp://' . $this->host . ':' . $this->port,
            $errorCode,
            $errorMessage,
            $this->connectionTimeout
        );

        if ($this->resource === false) {
            throw new \RuntimeException(sprintf('Could not create resource: %s', $errorMessage), $errorCode);
        }

        restore_error_handler();
    }
}