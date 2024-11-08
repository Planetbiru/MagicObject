<?php

namespace MagicObject\File;

/**
 * Class PicoDownloadFile
 *
 * This class handles the download of a file, supporting partial content requests (range requests).
 * It allows a file to be served to the client in chunks, ensuring that large files can be downloaded 
 * without requiring the entire file to be loaded into memory. It also checks if the requested file exists 
 * and handles potential errors appropriately.
 *
 * **Key Features**:
 * - Supports downloading entire files or partial file ranges.
 * - Provides proper HTTP headers for file transfer and range requests.
 * - Handles errors such as missing files or invalid range requests.
 *
 * **Usage**:
 * - Instantiate the class with the file path as a parameter.
 * - Call the `download()` method to initiate the download process.
 * 
 * Example:
 * ```php
 * $file = new PicoDownloadFile('/path/to/file.zip');
 * $file->download();
 * ```
 * 
 * @package MagicObject\File
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class PicoDownloadFile
{
    /**
     * The file path to the file being downloaded.
     *
     * @var string
     */
    private $file;

    /**
     * PicoDownloadFile constructor.
     *
     * Initializes the class with the file path to be downloaded.
     *
     * @param string $file The file path of the file to be downloaded.
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Initiates the download of the file.
     *
     * This method handles the HTTP headers for a proper file download, including partial content support
     * if the client requests a specific range. It will read the requested file and send it in chunks to the client.
     * If the file does not exist, it will return a 404 response. If an invalid range is requested, a 416 error will 
     * be sent. If everything is valid, the file will be served to the client.
     *
     * **Process**:
     * - Verifies that the file exists.
     * - Parses and handles byte range requests if provided by the client.
     * - Sends the appropriate HTTP headers for partial or full file downloads.
     * - Streams the file in chunks to the client.
     * 
     * @return void
     */
    public function download()
    {
        // Ensure the file exists
        if (!file_exists($this->file)) {
            header("HTTP/1.1 404 Not Found");
            echo "File not found.";
            exit;
        }

        // Get file size
        $fileSize = filesize($this->file);

        // Handle Range requests from the client
        if (isset($_SERVER['HTTP_RANGE'])) {
            // Format Range: bytes=start-end
            list($range, $extra) = explode(',', $_SERVER['HTTP_RANGE'], 2);
            list($start, $end) = explode('-', $range);
            $start = (int) $start;
            $end = $end ? (int) $end : $fileSize - 1;
        } else {
            // If no range is provided, send the entire file
            $start = 0;
            $end = $fileSize - 1;
        }

        // Ensure the range is valid
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            header("HTTP/1.1 416 Range Not Satisfiable");
            header("Content-Range: bytes 0-0/$fileSize");
            exit;
        }

        // Send response headers for byte-range support
        header('HTTP/1.1 206 Partial Content');
        header("Content-Type: application/octet-stream");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . basename($this->file) . "\"");
        header("Content-Range: bytes $start-$end/$fileSize");
        header("Content-Length: " . ($end - $start + 1));
        header("Accept-Ranges: bytes");

        // Open the file for reading
        $fp = fopen($this->file, 'rb');
        if ($fp === false) {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Failed to open file.";
            exit;
        }

        // Set the file pointer to the requested start position
        fseek($fp, $start);

        // Read and send the requested file chunk by chunk
        $bufferSize = 1024 * 8; // 8 KB buffer size, adjustable
        while (!feof($fp) && ftell($fp) <= $end) {
            echo fread($fp, $bufferSize);
            flush();
        }

        fclose($fp);
        exit;
    }
}
