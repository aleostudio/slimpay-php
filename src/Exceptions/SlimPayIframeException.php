<?php
/**
 * This file is part of the SlimPay Iframe package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LunaLabs\SlimPayIframe\Exceptions;

use \Exception;


class SlimPayIframeException extends Exception
{
    /**
     * Constructor.
     *
     * @param string          $error
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(string $error, int $code = 0, Exception $previous = null)
    {
        parent::__construct($error, $code, $previous);
    }
}
