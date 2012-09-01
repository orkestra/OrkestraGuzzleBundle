<?php
namespace Orkestra\Bundle\GuzzleBundle\Plugin;

use Guzzle\Common\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutions.co.uk>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Adds WSSE auth headers based on http://www.xml.com/pub/a/2003/12/17/dive.html
 *
 * @see    http://www.xml.com/pub/a/2003/12/17/dive.html
 * @author Dave Marshall <dave.marshall@atstsolutions.co.uk>
 */
class WsseAuthPlugin implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Callable
     */
    private $digester;

    /**
     * @var Callable
     */
    private $noncer;

    /**
     * Constructor
     *
     * @param string   $username  The username
     * @param string   $password  The password
     * @param Callable $digester  Optional closure to create digest
     * @param Callable $noncer    Optional closure to create nonce
     */
    public function __construct($username, $password, $digester = null, $noncer = null)
    {
        $this->username = $username;
        $this->password = $password;

        $this->noncer = array($this, 'noncer');
        $this->digester = array($this, 'digester');

        if ($digester !== null) {
            if (!is_callable($digester)) {
                throw new \InvalidArgumentException("\$digester must be callable, " . gettype($digester) . " passed");
            }
            $this->digester = $digester;
        }

        if ($noncer !== null) {
            if (!is_callable($noncer)) {
                throw new \InvalidArgumentException("\$noncer must be callable, " . gettype($noncer) . " passed");
            }
            $this->noncer = $noncer;
        }
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array('client.create_request' => 'onRequestCreate');
    }

    /**
     * Add WSSE auth headers
     *
     * @param Event $event
     */
    public function onRequestCreate(Event $event)
    {
        $request = $event['request'];

        $nonce = call_user_func($this->noncer);
        $created = date('r');
        $digest = call_user_func($this->digester, $nonce, $created, $this->password);

        $request->addHeaders(array(
            "Authorization" => "WSSE profile=\"UsernameToken\"",
            "X-WSSE" => "UsernameToken Username=\"{$this->username}\", PasswordDigest=\"$digest\", Nonce=\"$nonce\", Created=\"$created\"",
        ));
    }

    /**
     * Digest
     *
     * @param string $nonce
     */
    public static function digester($nonce, $created, $password)
    {
        return base64_encode(sha1(base64_decode($nonce) . $created . $password, true));
    }

    /**
     * Noncer
     *
     * @return string
     */
    public static function noncer()
    {
        return hash('sha512', uniqid(true));
    }


}