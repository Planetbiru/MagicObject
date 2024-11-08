<?php

namespace MagicObject\File;

/**
 * Class PicoDownloadFile
 *
 * This class facilitates downloading a file, supporting partial content requests (range requests),
 * which allows the file to be transferred in chunks. This is particularly useful for large files
 * where downloading the entire file in one go might be inefficient or unfeasible. The class also
 * ensures that the requested file exists and handles various errors such as missing files and invalid
 * range requests gracefully.
 *
 * **Key Features**:
 * - Supports downloading the full file or a partial file range (e.g., for resuming interrupted downloads).
 * - Sends proper HTTP headers for file transfer and range requests.
 * - Handles errors such as non-existent files or invalid range requests (HTTP 404 and 416).
 *
 * **Usage**:
 * - Instantiate the class with the file path and an optional filename parameter.
 * - Call the `download()` method to trigger the file download.
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
     * The path to the file being downloaded.
     *
     * @var string
     */
    private $filepath;

    /**
     * The name of the file to be sent to the client. If not provided, the filename will be
     * inferred from the file's path.
     *
     * @var string
     */
    private $filename;

    /**
     * PicoDownloadFile constructor.
     *
     * Initializes the class with the file path to be downloaded and an optional filename.
     * If no filename is provided, the class will use the base name of the file path.
     *
     * @param string $filepath The full path to the file to be downloaded.
     * @param string|null $filename The name of the file to be sent in the download response (optional).
     */
    public function __construct($filepath, $filename = null)
    {
        $this->filepath = $filepath;
        // If no filename is provided, use the basename of the file path
        if (!isset($filename)) {
            $filename = basename($filepath);
        }
        $this->filename = $filename;
    }

    /**
     * Initiates the download of the file.
     *
     * This method sends the appropriate HTTP headers to facilitate the download of a file, 
     * including support for partial content (range requests). The method handles:
     * - Verifying the file's existence.
     * - Parsing and handling byte range requests (for resuming downloads).
     * - Sending appropriate HTTP headers for file transfer.
     * - Streaming the file to the client in chunks (if applicable).
     * 
     * If the file doesn't exist, a 404 error is sent. If the range is invalid, a 416 error is sent.
     * If the file exists and everything is valid, the file will be served to the client.
     *
     * **Process**:
     * - Verifies that the file exists at the provided path.
     * - Parses the range header (if any) and determines the appropriate byte range.
     * - Sends headers for partial or full content transfer.
     * - Streams the file in chunks to the client.
     * 
     * @return void
     */
    public function download()
    {
        // Ensure the file exists
        if (!file_exists($this->filepath)) {
            header("HTTP/1.1 404 Not Found");
            echo "File not found.";
            exit;
        }

        // Get the file size
        $fileSize = filesize($this->filepath);

        // Handle range requests if provided by the client
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

        // Send response headers for partial content (range requests)
        header('HTTP/1.1 206 Partial Content');
        header("Content-Type: application/octet-stream");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . $this->filename . "\"");
        header("Content-Range: bytes $start-$end/$fileSize");
        header("Content-Length: " . ($end - $start + 1));
        header("Accept-Ranges: bytes");

        // Open the file for reading
        $fp = fopen($this->filepath, 'rb');
        if ($fp === false) {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Failed to open file.";
            exit;
        }

        // Move the pointer to the start position
        fseek($fp, $start);

        // Read and send the file in chunks (8 KB buffer size)
        $bufferSize = 1024 * 8; // 8 KB buffer size
        while (!feof($fp) && ftell($fp) <= $end) {
            echo fread($fp, $bufferSize);
            flush();
        }

        fclose($fp);
        exit;
    }
}
