<?php

use MagicObject\Util\PicoParsedown;

require_once dirname(__DIR__) . "/vendor/autoload.php";

require_once dirname(__DIR__) . "/src/Util/PicoParsedown.php";

// Fungsi untuk membaca file dalam direktori secara rekursif dan bertingkat
function scanDirectory($dir, $maxDepth = PHP_INT_MAX, $currentDepth = 0) {
    $phpFiles = [];

    // Menghindari lebih dalam jika kedalaman lebih dari batas
    if ($currentDepth > $maxDepth) {
        return $phpFiles;
    }

    // Mengecek apakah direktori valid
    if (!is_dir($dir)) {
        return $phpFiles;
    }

    $directoryIterator = new RecursiveDirectoryIterator($dir);
    $iterator = new RecursiveIteratorIterator(
        $directoryIterator,
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        // Mengabaikan direktori dan hanya mengambil file PHP
        if ($file->isFile() && $file->getExtension() == 'php') {
            $phpFiles[] = $file->getRealPath();
        }
    }

    return $phpFiles;
}
// Fungsi untuk memparse docblock menjadi array yang lebih terstruktur
function parseDocblock($docblock) {
    
    $parsed = [
        'description' => '',
        'tags' => []
    ];

    // Menghapus tanda komentar awal dan akhir
    $docblock = trim($docblock, "/* \n");

    // Hapus bintang (*) di awal setiap baris deskripsi
    $docblock = preg_replace('/^\s*\*/m', '', $docblock);

    // Pisahkan docblock menjadi deskripsi umum dan tag
    preg_match('/\s*(.*?)\s*(\s+\@.*)?$/s', $docblock, $matches);

    $parsed['description'] = trim($matches[1]);

    // Cari tag dalam docblock
    if (isset($matches[2])) {
        preg_match_all('/\s*\@(\w+)\s+([^\n]+)/', $matches[2], $tagMatches, PREG_SET_ORDER);
        foreach ($tagMatches as $tagMatch) {
            $parsed['tags'][] = [
                'tag' => $tagMatch[1],
                'description' => trim($tagMatch[2])
            ];
        }
    }

    return $parsed;
}

// Fungsi untuk menampilkan docblock yang sudah diparse
function displayParsedDocblock($parsedDocblock) {
    $parsedown = new PicoParsedown();
    if ($parsedDocblock['description']) {
        echo "<strong>Description:</strong><br>\n";
        echo $parsedown->text($parsedDocblock['description']) . "\n";
    }

    if (!empty($parsedDocblock['tags'])) {
        foreach ($parsedDocblock['tags'] as $tag) {
            echo "<strong>@{$tag['tag']}:</strong> {$tag['description']}<br>\n";
        }
        echo "<br>\n";
    }
}

// Fungsi untuk mendapatkan docblock dari kelas, properti, dan metode PHP
function getAllDocblocks($file) {
    $fileContents = file_get_contents($file);
    
    include_once $file;
    // Cek apakah file memiliki namespace
    preg_match('/namespace\s+([a-zA-Z0-9\\\_]+)/', $fileContents, $namespaceMatches);
    $namespace = isset($namespaceMatches[1]) ? $namespaceMatches[1] : '';

    // Gunakan regular expression untuk mencari definisi kelas
    preg_match_all('/class\s+(\w+)/', $fileContents, $matches);
    
    //if (!empty($matches[1])) 
    {
        //foreach ($matches[1] as $className) 
        $className = basename($file, '.php');
        {
            // Jika kelas berada dalam namespace, tambahkan namespace
            $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

            try {
                // Mencoba untuk merefleksikan kelas
                $reflection = new ReflectionClass($fullClassName);
                echo "<h1>Class: {$fullClassName}</h1>\n";
                
                // Mendapatkan docblock kelas
                $classDocblock = $reflection->getDocComment();
                if ($classDocblock) {
                    $parsedClassDocblock = parseDocblock($classDocblock);
                    echo "<div class='docblock'>\n";
                    echo "<strong>Class Docblock:</strong><br>\n";
                    displayParsedDocblock($parsedClassDocblock);
                    echo "</div>\n";
                }
                
                // Mengambil docblock dari properti
                foreach ($reflection->getProperties() as $property) {
                    $propertyDocblock = $property->getDocComment();
                    if ($propertyDocblock) {
                        $parsedPropertyDocblock = parseDocblock($propertyDocblock);
                        echo "<div class='property'>\n";
                        echo "<h2>Property: {$property->getName()}</h2>\n";
                        echo "<div class='docblock'>\n";
                        echo "<strong>Property Docblock:</strong><br>\n";
                        displayParsedDocblock($parsedPropertyDocblock);
                        echo "</div>\n";
                        echo "</div>\n";
                    }
                }
                
                // Mengambil docblock dari metode
                foreach ($reflection->getMethods() as $method) {
                    $methodDocblock = $method->getDocComment();
                    if ($methodDocblock) {
                        $parsedMethodDocblock = parseDocblock($methodDocblock);
                        echo "<div class='method'>\n";
                        echo "<h2>Method: {$method->getName()}</h2>\n";
                        echo "<div class='docblock'>\n";
                        echo "<strong>Method Docblock:</strong><br>\n";
                        displayParsedDocblock($parsedMethodDocblock);
                        echo "</div>\n";
                        echo "</div>\n";
                    }
                }
                
            } catch (ReflectionException $e) {
                echo "Could not reflect on class {$fullClassName}: " . $e->getMessage() . "<br>\n";
            }
        }
    }

}

// Mendapatkan direktori sumber dari repositori
$srcDir = dirname(__DIR__) . '/src';  // Anda bisa sesuaikan dengan direktori proyek Anda

// Pastikan direktori ada
if (is_dir($srcDir)) {
    // Scan direktori untuk menemukan file PHP
    $files = scanDirectory($srcDir);
    
    
    
    // Proses setiap file untuk mencari dan menampilkan docblock kelas, properti, dan metode
    foreach ($files as $idx=>$file) {
        getAllDocblocks($file);
    }
} else {
    echo "Direktori src tidak ditemukan. Pastikan Anda menjalankan skrip ini di dalam repositori proyek.\n";
}

?>
