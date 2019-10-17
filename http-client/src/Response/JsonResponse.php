<?php

namespace DNA\HttpClient\Response;

use DNA\HttpClient\Exception\InvalidArgumentException;
use DNA\HttpClient\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use function gettype;
use function is_float;
use function is_numeric;
use function is_scalar;
use function sprintf;
use function strtolower;
use function array_merge;
use function get_class;
use function is_object;
use function is_string;
use function array_keys;
use function array_reduce;
use function is_resource;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_ERROR_NONE;

final class JsonResponse implements ResponseInterface
{
    private $protocol = '1.1';

    private $headers = [];

    private $headerNames = [];

    private $stream;

    private $reasonPhrase;

    private $statusCode;

    public const MIN_STATUS_CODE_VALUE = 100;
    public const MAX_STATUS_CODE_VALUE = 599;
    public const DEFAULT_JSON_FLAGS = 79;

    private $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated to 306 => '(Unused)'
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    private $payload;

    private $encodingOptions;

    public function __construct(
        $data,
        int $status = 200,
        array $headers = [],
        int $encodingOptions = self::DEFAULT_JSON_FLAGS
    ) {
        $this->setPayload($data);
        $this->encodingOptions = $encodingOptions;
        $json = $this->jsonEncode($data, $this->encodingOptions);
        $body = $this->createBodyFromJson($json);
        $headers = $this->injectContentType('application/json', $headers);
        $this->setStatusCode($status);
        $this->stream = $this->getStream($body, 'wb+');
        $this->setHeaders($headers);
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function withPayload($data) : JsonResponse
    {
        $new = clone $this;
        $new->setPayload($data);
        return $this->updateBodyFor($new);
    }

    public function getEncodingOptions() : int
    {
        return $this->encodingOptions;
    }

    public function withEncodingOptions(int $encodingOptions) : JsonResponse
    {
        $new = clone $this;
        $new->encodingOptions = $encodingOptions;
        return $this->updateBodyFor($new);
    }

    private function createBodyFromJson(string $json): Stream
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($json);
        $body->rewind();

        return $body;
    }

    /**
     * Encode the provided data to JSON.
     *
     * @param mixed $data
     *
     * @param int   $encodingOptions
     *
     * @return string
     */
    private function jsonEncode($data, int $encodingOptions) : string
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException('Cannot JSON encode resources');
        }

        // Clear json_last_error()
        json_encode(null);
        $json = json_encode($data, $encodingOptions);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to encode data to JSON in %s: %s',
                __CLASS__,
                json_last_error_msg()
            ));
        }

        return $json;
    }
    /**
     * @param mixed $data
     */
    private function setPayload($data) : void
    {
        if (is_object($data)) {
            $data = clone $data;
        }
        $this->payload = $data;
    }
    /**
     * Update the response body for the given instance.
     *
     * @param self $toUpdate Instance to update.
     * @return JsonResponse Returns a new instance with an updated body.
     */
    private function updateBodyFor(JsonResponse $toUpdate) : JsonResponse
    {
        $json = $this->jsonEncode($toUpdate->payload, $toUpdate->encodingOptions);
        $body = $this->createBodyFromJson($json);
        return $toUpdate->withBody($body);
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     *
     * @return static
     */
    public function withProtocolVersion($version): MessageInterface
    {
        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
        }
        $header = $this->headerNames[strtolower($name)];

        return $this->headers[$header];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name): string
    {
        $value = $this->getHeader($name);
        if (empty($value)) {
            return '';
        }

        return implode(',', $value);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value): MessageInterface
    {
        $normalized = strtolower($name);

        $new = clone $this;
        if ($new->hasHeader($name)) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        $new->headerNames[$normalized] = $name;
        $new->headers[$name]         = $value;

        return $new;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $header = $this->headerNames[strtolower($name)];
        $new = clone $this;
        $new->headers[$header] = array_merge($this->headers[$header], $value);

        return $new;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return static
     */
    public function withoutHeader($name): MessageInterface
    {
        if (!$this->hasHeader($name)) {
            return clone $this;
        }

        $normalized = strtolower($name);
        $original   = $this->headerNames[$normalized];
        $new = clone $this;
        unset($new->headers[$original], $new->headerNames[$normalized]);

        return $new;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     *
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     *
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = ''): MessageInterface
    {
        $new = clone $this;
        $new->setStatusCode($code, $reasonPhrase);

        return $new;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    private function getStream($stream, string $modeIfNotInstance) : StreamInterface
    {
        if ($stream instanceof StreamInterface) {
            return $stream;
        }
        if (! is_string($stream) && ! is_resource($stream)) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }
        return new Stream($stream, $modeIfNotInstance);
    }

    private function setHeaders(array $originalHeaders) : void
    {
        $headerNames = $headers = [];
        foreach ($originalHeaders as $header => $value) {
            $headerNames[strtolower($header)] = $header;
            $headers[$header] = $value;
        }
        $this->headerNames = $headerNames;
        $this->headers = $headers;
    }

    private function setStatusCode($code, $reasonPhrase = '') : void
    {
        if (! is_numeric($code)
            || is_float($code)
            || $code < static::MIN_STATUS_CODE_VALUE
            || $code > static::MAX_STATUS_CODE_VALUE
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between %d and %d, inclusive',
                is_scalar($code) ? $code : gettype($code),
                static::MIN_STATUS_CODE_VALUE,
                static::MAX_STATUS_CODE_VALUE
            ));
        }
        if (! is_string($reasonPhrase)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported response reason phrase; must be a string, received %s',
                is_object($reasonPhrase) ? get_class($reasonPhrase) : gettype($reasonPhrase)
            ));
        }
        if ($reasonPhrase === '' && isset($this->phrases[$code])) {
            $reasonPhrase = $this->phrases[$code];
        }
        $this->reasonPhrase = $reasonPhrase;
        $this->statusCode = (int) $code;
    }

    private function injectContentType(string $contentType, array $headers) : array
    {
        $hasContentType = array_reduce(array_keys($headers), static function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }
        return $headers;
    }
}
