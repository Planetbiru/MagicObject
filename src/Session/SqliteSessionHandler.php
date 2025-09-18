<?php

namespace MagicObject\Session;

use MagicObject\Exceptions\InvalidFileAccessException;
use PDO;

/**
 * Class SqliteSessionHandler
 *
 * A custom session handler implementation using SQLite as the storage backend.
 * This class manages session lifecycle including create, read, update,
 * destroy, and garbage collection.
 */
class SqliteSessionHandler
{
    /**
     * PDO instance for SQLite connection.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Table name used for storing session data.
     *
     * @var string
     */
    private $table = "sessions";

    /**
     * Constructor.
     *
     * Ensures the database file and its parent directory exist,
     * then initializes the SQLite connection and creates the
     * sessions table if it does not exist.
     *
     * Behavior:
     * - If the provided path points to a directory, a default filename
     *   `sessions.sqlite` will be appended.
     * - If the file does not exist, the directory will be created (if missing),
     *   write access will be checked, and an empty file will be created.
     * - If the path is still a directory after these checks, an exception is thrown.
     *
     * @param string $path Absolute path to the SQLite database file or directory.
     *
     * @throws InvalidFileAccessException If the target directory is not writable
     *                                    or the path cannot be resolved.
     */
    public function __construct($path)
    {
        // If the path is a directory, append a default filename
        if (is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "sessions.sqlite";
        }

        // If the file does not exist
        if (!file_exists($path)) {
            $dir = dirname($path);

            // Create the directory if it does not exist
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new InvalidFileAccessException("Failed to create directory: " . $dir);
                }
            }

            // Ensure the directory is writable
            if (!is_writable($dir)) {
                throw new InvalidFileAccessException("Folder not writable: " . $dir);
            }

            // Create an empty file for SQLite
            file_put_contents($path, "");
        }

        // Validation: if the path is still a directory (edge case)
        if (is_dir($path)) {
            throw new InvalidFileAccessException("Target path is a directory, expected a file: " . $path);
        }

        // Resolve the real path
        $real = realpath($path);
        if ($real === false) {
            throw new InvalidFileAccessException("Cannot resolve path: " . $path);
        }

        // Build DSN for SQLite
        $dsn = "sqlite:" . $real;
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create the sessions table if it does not exist
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id TEXT PRIMARY KEY,
                data TEXT,
                timestamp INTEGER
            )
        ");
    }

    /**
     * Open the session.
     *
     * @param string $savePath    Session save path.
     * @param string $sessionName Session name.
     *
     * @return bool Always true.
     */
    public function open($savePath, $sessionName) // NOSONAR
    {
        return true;
    }

    /**
     * Close the session.
     *
     * @return bool Always true.
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data by session ID.
     *
     * @param string $id Session ID.
     *
     * @return string Serialized session data, or empty string if not found.
     */
    public function read($id)
    {
        $stmt = $this->pdo->prepare("SELECT data FROM {$this->table} WHERE id = :id");
        $stmt->execute(array(':id' => $id));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($row['data']) ? $row['data'] : '';
    }

    /**
     * Write session data.
     *
     * @param string $id   Session ID.
     * @param string $data Serialized session data.
     *
     * @return bool True on success.
     */
    public function write($id, $data)
    {
        $time = time();
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (id, data, time_creation)
            VALUES (:id, :data, :time)
            ON CONFLICT(id) DO UPDATE SET data = :data, time_creation = :time
        ");

        return $stmt->execute(array(
            ':id'   => $id,
            ':data' => $data,
            ':time' => $time
        ));
    }

    /**
     * Destroy a session by ID.
     *
     * @param string $id Session ID.
     *
     * @return bool True on success.
     */
    public function destroy($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }

    /**
     * Perform garbage collection.
     *
     * Removes expired sessions older than max lifetime.
     *
     * @param int $maxlifetime Maximum lifetime in seconds.
     *
     * @return bool True if query executed successfully.
     */
    public function gc($maxlifetime)
    {
        $old = time() - $maxlifetime;
        return $this->pdo->exec("DELETE FROM {$this->table} WHERE time_creation < $old") !== false;
    }
}
