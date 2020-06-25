<?php
/**
 * This file is part of the SlimPay PHP package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AleoStudio\SlimPayPhp\Http;

use ArrayAccess;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use InvalidArgumentException;


class Response implements ResponseInterface, ArrayAccess
{
    /**
     * @var mixed
     */
    public $data;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;


    /**
     * Response constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->data = $this->getDataFromResponse($response);
    }


    /**
     * Get the api data from the response as usual.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data->{$name};
    }


    /**
     * Get the underlying data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Return an array of the data.
     *
     * @return array
     */
    public function toArray()
    {
        return json_decode(json_encode($this->data), true);
    }


    /**
     * Whether a offset exists.
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data->{$offset});
    }


    /**
     * Offset to retrieve.
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $data = $this->toArray();

        return $data[$offset];
    }


    /**
     * Offset to set.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data->{$offset} = $value;
    }


    /**
     * Offset to unset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data->{$offset});
    }


    /**
     * Retrieves the HTTP protocol version as a string.
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version
     */
    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }


    /**
     * Return an instance with the specified HTTP protocol version.
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param  string $version HTTP protocol version
     * @return ResponseInterface
     */
    public function withProtocolVersion($version)
    {
        return $this->response->withProtocolVersion($version);
    }


    /**
     * Retrieves all message header values.
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
     * @return array Returns an associative array of the message's headers. Each
     *               key MUST be a header name, and each value MUST be an array of strings
     *               for that header.
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }


    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param  string $name case-insensitive header field name
     * @return bool Returns true if any header names match the given header
     *              name using a case-insensitive string comparison. Returns false if
     *              no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }


    /**
     * Retrieves a message header value by the given case-insensitive name.
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param  string $name case-insensitive header field name
     * @return string[] An array of string values as provided for the given
     *                  header. If the header does not appear in the message, this method MUST
     *                  return an empty array.
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }


    /**
     * Retrieves a comma-separated string of the values for a single header.
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param  string $name case-insensitive header field name
     * @return string A string of values as provided for the given header
     *                concatenated together using a comma. If the header does not appear in
     *                the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }


    /**
     * Return an instance with the provided value replacing the specified header.
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param  string          $name  case-insensitive header field name
     * @param  string|string[] $value header value(s)
     * @return ResponseInterface
     * @throws InvalidArgumentException for invalid header names or values
     */
    public function withHeader($name, $value)
    {
        return $this->response->withHeader($name, $value);
    }


    /**
     * Return an instance with the specified header appended with the given value.
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param  string          $name  case-insensitive header field name to add
     * @param  string|string[] $value header value(s)
     * @return ResponseInterface
     * @throws InvalidArgumentException for invalid header names or values
     */
    public function withAddedHeader($name, $value)
    {
        return $this->response->withAddedHeader($name, $value);
    }


    /**
     * Return an instance without the specified header.
     * Header resolution MUST be done without case-sensitivity.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param  string $name case-insensitive header field name to remove
     * @return ResponseInterface
     */
    public function withoutHeader($name)
    {
        return $this->response->withoutHeader($name);
    }


    /**
     * Gets the body of the message.
     *
     * @return StreamInterface returns the body as a stream
     */
    public function getBody()
    {
        return $this->response->getBody();
    }


    /**
     * Return an instance with the specified message body.
     * The body MUST be a StreamInterface object.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param  StreamInterface $body body
     * @return ResponseInterface
     * @throws InvalidArgumentException when the body is not valid
     */
    public function withBody(StreamInterface $body)
    {
        return $this->response->withBody($body);
    }


    /**
     * Gets the response status code.
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int status code
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }


    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-6
     * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code         the 3-digit integer result code to set
     * @param string $reasonPhrase the reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification
     *
     * @throws InvalidArgumentException for invalid status code arguments
     * @return ResponseInterface
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->response->withStatus($code, $reasonPhrase);
    }


    /**
     * Gets the response reason phrase associated with the status code.
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-6
     * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @return string reason phrase; must return an empty string if none present
     */
    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }


    /**
     * @return mixed
     */
    private function getDataFromResponse(ResponseInterface $response)
    {
        $contents = $response->getBody()->getContents();

        return $contents ? json_decode($contents) : null;
    }


    /**
     * Through the response content type, handles the response to always return an object.
     * Optionally, it can be passed the object key to extract.
     *
     * @param  string $key
     * @return object
     */
    private function parseResponse(string $key = null): object
    {
        $response    = (object)[];
        $contentType = $this->response->getHeader('Content-Type');
        $contentType = (is_array($contentType)) ? $contentType[0] : null;

        if ($contentType) {
            if (strpos($contentType, 'json') !== false) {
                $response = (object) json_decode($this->response->getBody());
            } elseif (strpos($contentType, 'xml') !== false) {
                $encoded  = json_encode(simplexml_load_string($this->response->getBody()));
                $response = (object) json_decode($encoded, true);
            }
        }

        if ($key) {
            $response = (object) $response->{$key};
        }

        return $response;
    }


    /**
     * Returns the response as valid JSON string.
     *
     * @param  string $key
     * @return string
     */
    public function toJson(string $key = null): string
    {
        return json_encode($this->parseResponse($key));
    }


    /**
     * Returns the response as valid StdObject.
     *
     * @param  string $key
     * @return object
     */
    public function toObject(string $key = null): object
    {
        return $this->parseResponse($key);
    }
}
