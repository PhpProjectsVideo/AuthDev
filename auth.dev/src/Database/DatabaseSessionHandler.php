<?php

namespace PhpProjects\AuthDev\Database;

/**
 * Handles storing our sessions in the database. Use with session_set_save_handler to enable DB backed sessions.
 */
class DatabaseSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * Set to true to force garbage collection on session close.
     * @var bool
     */
    private $runGc = false;

    /**
     * DatabaseSessionHandler constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $session_id)
    {
        //Don't really need to do anything here
        return true;
    }

    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $sessionId The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($sessionId)
    {
        //Get App level DB lock so we don't have a ginormous transaction
        $this->sessionId = $sessionId;
        $stm = $this->pdo->prepare("SELECT GET_LOCK(:key, 60)");
        $stm->execute(['key' => 'SESSION_' . $this->sessionId]);

        //get the session data
        $readStm = $this->pdo->prepare("SELECT * FROM session WHERE session_id = :sessionId");
        $readStm->execute([ 'sessionId' => $sessionId ]);

        if ($row = $readStm->fetch())
        {
            if ($row['expiration'] < time())
            {
                $this->destroy($sessionId);
            }
            else
            {
                return $row['data'];
            }
        }
        return '';
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $sessionId The session id.
     * @param string $sessionData <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($sessionId, $sessionData)
    {
        $stm = $this->pdo->prepare("
          INSERT INTO session (session_id, data, expiration) 
          VALUES (:sessionId, :sessionData, :expiration) 
          ON DUPLICATE KEY UPDATE data = VALUES(data), expiration = VALUES(expiration)
        ");

        $stm->execute([
            'sessionId' => $sessionId,
            'sessionData' => $sessionData,
            'expiration' => time() + ((int) ini_get('session.gc_maxlifetime')),
        ]);

        return true;
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        // Let go of our app level db lock
        $stm = $this->pdo->prepare("DO RELEASE_LOCK(:key)");
        $stm->execute(['key' => 'SESSION_' . $this->sessionId]);

        //Clean up the expired sessions if requested
        if ($this->runGc)
        {
            $deleteStm = $this->pdo->prepare("DELETE FROM session WHERE expiration < :time");
            $deleteStm->execute(['time' => time()]);
        }
        
        return true;
    }

    /**
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function gc($maxlifetime)
    {
        //Delay running gc until the end of the request
        $this->runGc = true;

        return true;
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $sessionId The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($sessionId)
    {
        $stm = $this->pdo->prepare("DELETE FROM session WHERE session_id = :sessionId");
        $stm->execute(['sessionId' => $sessionId]);
        
        return true;
    }
}