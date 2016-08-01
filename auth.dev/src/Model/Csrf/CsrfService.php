<?php

namespace PhpProjects\AuthDev\Model\Csrf;

/**
 * Manages csrf tokens for our application. You can read more about CSRF at 
 * https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)
 */
class CsrfService
{
    /**
     * The key to use in generated the csrf token. Keep this secret.
     */
    const CONFIG_KEY = 'defaultkey';

    /**
     * How long csrf tokens are good for
     */
    const CONFIG_TTL = 1800;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var array
     */
    private $store;

    /**
     * @var int
     */
    private $ttlSeconds;

    /**
     * @param string $key
     * @param string $sessionId
     * @param array $store
     * @param int $ttlSeconds
     */
    public function __construct(string $key, string $sessionId, array &$store, int $ttlSeconds)
    {
        $this->key = $key;
        $this->sessionId = $sessionId;
        $this->store =& $store;
        $this->ttlSeconds = $ttlSeconds;
    }

    /**
     * @return CsrfService
     */
    public static function create() : CsrfService
    {
        $key = defined('CONFIG_CSRF_KEY') ? CONFIG_CSRF_KEY : self::CONFIG_KEY;
        $ttlSeconds = defined('CONFIG_CSRF_TTL') ? CONFIG_CSRF_TTL : self::CONFIG_TTL;
        return new CsrfService($key, session_id(), $_SESSION, $ttlSeconds);
    }

    /**
     * Creates a new one time use token. It will be added to the csrf store.
     * 
     * @return string
     */
    public function getNewToken() : string
    {
        $currentTime = time();
        $data = [
            'key' => $this->key,
            'sessionId' => $this->sessionId,
            'random' => base64_encode(random_bytes(16)),
            'time' => $currentTime,
        ];

        $hash = hash('gost', http_build_query($data));
        $this->store['__csrf'][$hash] = $currentTime;
        return $hash;
    }

    /**
     * Validates the passed in token. Returns true if it is valid, false otherwise. If it is valid it is removed from 
     * the store.
     * 
     * @param string $token
     * @return bool
     */
    public function validateToken(string $token) : bool 
    {
        if (!empty($this->store['__csrf'][$token]))
        {
            $time = $this->store['__csrf'][$token];
            unset($this->store['__csrf'][$token]);
            
            if ($time + $this->ttlSeconds > time())
            {
                return true;
            }
        }
        
        return false;
    }
}