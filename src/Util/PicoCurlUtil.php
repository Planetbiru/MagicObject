<?php

namespace MagicObject\Util;

use CurlHandle;
use Exception;
use MagicObject\Exceptions\CurlException;

/**
 * Class Curl
 *
 * Kelas ini menyediakan antarmuka untuk melakukan permintaan HTTP menggunakan cURL.
 */
class PicoCurlUtil {

    /**
     * Curl handle
     *
     * @var CurlHandle
     */
    private $curl;

    /** 
     * @var array $responseHeaders Header respons dari permintaan terakhir 
     */
    private $responseHeaders = [];

    /** 
     * @var string $responseBody Body respons dari permintaan terakhir 
     */
    private $responseBody = '';

    /** 
     * @var int $httpCode Kode status HTTP dari permintaan terakhir 
     */
    private $httpCode = 0;

    /**
     * Curl constructor.
     * Menginisialisasi handle cURL.
     */
    public function __construct() {
        $this->curl = curl_init();
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_HEADER, true); // Untuk menangkap header
    }

    /**
     * Mengatur opsi cURL.
     *
     * @param int $option Opsi cURL yang akan diatur
     * @param mixed $value Nilai untuk opsi cURL
     */
    public function setOption($option, $value) {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     * Melakukan permintaan GET.
     *
     * @param string $url URL untuk permintaan
     * @param array $headers Header tambahan untuk permintaan
     * @return string Body respons
     */
    public function get($url, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Melakukan permintaan POST.
     *
     * @param string $url URL untuk permintaan
     * @param mixed $data Data yang akan dikirim
     * @param array $headers Header tambahan untuk permintaan
     * @return string Body respons
     */
    public function post($url, $data, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, $data);
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Melakukan permintaan PUT.
     *
     * @param string $url URL untuk permintaan
     * @param mixed $data Data yang akan dikirim
     * @param array $headers Header tambahan untuk permintaan
     * @return string Body respons
     */
    public function put($url, $data, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
        $this->setOption(CURLOPT_POSTFIELDS, $data);
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Melakukan permintaan DELETE.
     *
     * @param string $url URL untuk permintaan
     * @param array $headers Header tambahan untuk permintaan
     * @return string Body respons
     */
    public function delete($url, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_CUSTOMREQUEST, "DELETE");
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Menjalankan permintaan cURL dan memproses respons.
     *
     * @return string Body respons
     * @throws Exception Jika terjadi kesalahan saat menjalankan cURL
     */
    private function execute() {
        $response = curl_exec($this->curl);
        if ($response === false) {
            throw new CurlException('Curl error: ' . curl_error($this->curl));
        }

        // Pisahkan header dan body
        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $this->responseHeaders = explode("\r\n", substr($response, 0, $headerSize));
        $this->responseBody = substr($response, $headerSize);
        $this->httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        return $this->responseBody;
    }

    /**
     * Mendapatkan kode status HTTP dari respons terakhir.
     *
     * @return int Kode status HTTP
     */
    public function getHttpCode() {
        return $this->httpCode;
    }

    /**
     * Mendapatkan header respons dari permintaan terakhir.
     *
     * @return array Array header respons
     */
    public function getResponseHeaders() {
        return $this->responseHeaders;
    }

    /**
     * Menutup handle cURL.
     */
    public function close() {
        curl_close($this->curl);
    }

    /**
     * Destructor untuk menutup cURL saat objek dihapus.
     */
    public function __destruct() {
        $this->close();
    }
}
