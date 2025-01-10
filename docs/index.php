<?php

use MagicObject\Util\PicoParsedown;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Class to scan PHP files in a directory recursively, parse docblocks, and display them.
 */
class PhpDocScanner {

    private $parsedown = null;

    /**
     * Constructor for the class.
     * Initializes the PicoParsedown object to be used within the class.
     */
    public function __construct()
    {
        $this->parsedown = new PicoParsedown();
    }

    /**
     * Scans a directory recursively for PHP files up to a certain depth.
     *
     * @param string $dir The directory to scan.
     * @param int $maxDepth The maximum depth of recursion. Default is PHP_INT_MAX.
     * @param int $currentDepth The current recursion depth. Used internally.
     * @return array Array of paths to PHP files.
     */
    public function scanDirectory($dir, $maxDepth = PHP_INT_MAX, $currentDepth = 0) {
        $phpFiles = [];

        // Avoid deeper scanning if depth exceeds the max limit
        if ($currentDepth > $maxDepth) {
            return $phpFiles;
        }

        // Ensure directory is valid
        if (!is_dir($dir)) {
            return $phpFiles;
        }

        // Scan PHP files in the current directory first
        $files = new DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $phpFiles[] = $file->getRealPath();
            }
        }

        // Recursively scan subdirectories
        $directoryIterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $phpFiles[] = $file->getRealPath();
            }
        }

        return $phpFiles;
    }

    /**
     * Parses a docblock into a structured array with description and tags.
     *
     * @param string $docblock The raw docblock text to parse.
     * @return array Parsed docblock containing description and tags.
     */
    public function parseDocblock($docblock) {
        $parsed = [
            'description' => '',
            'tags' => []
        ];

        // Trim the docblock and remove comment stars
        $docblock = trim($docblock, "/* \n");
        $docblock = preg_replace('/^\s*\*/m', '', $docblock);

        // Separate the description and tags
        preg_match('/\s*(.*?)\s*(\s+\@.*)?$/s', $docblock, $matches);
        $parsed['description'] = trim($matches[1]);

        // Extract tags from the docblock
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

    /**
     * Generates a parsed docblock with proper HTML formatting.
     *
     * @param array $parsedDocblock The parsed docblock to display.
     * @return string The HTML-formatted docblock.
     */
    public function generateParsedDocblock($parsedDocblock, $heading = "h3") {   
        $output = '';

        // Description section
        if ($parsedDocblock['description']) {
            $output .= "<$heading>Description</$heading>\r\n";
            $output .= $this->parsedown->text(trim($parsedDocblock['description'])) . "\n";
        }

        // Parameters section
        $parameters = $this->parseParameters($parsedDocblock);
        if (!empty($parameters)) {
            $output .= "<h3>Parameters</h3>\r\n";
            foreach ($parameters as $parameter) {
                $output .= "<div class=\"parameter-name\">{$parameter['name']}</div>\r\n";
                $output .= "<div class=\"parameter-description\">{$this->parsedown->text(ltrim($parameter['description']))}</div>\r\n";
            }
        }

        // Returns section
        $returns = $this->parseReturns($parsedDocblock);
        if (!empty($returns)) {
            $output .= "<h3>Return</h3>\r\n";
            foreach ($returns as $return) {
                $output .= "<div class=\"return-type\">{$return['type']}</div>\r\n";
                $output .= "<div class=\"return-description\">{$this->parsedown->text(ltrim($return['description']))}</div>\r\n";
            }
        }

        // Throws section
        $throws = $this->parseThrows($parsedDocblock);
        if (!empty($throws)) {
            $output .= "<h3>Throws</h3>\r\n";
            foreach ($throws as $throw) {
                $output .= "<div class=\"return-type\">{$throw['type']}</div>\r\n";
                $output .= "<div class=\"return-description\">{$this->parsedown->text($throw['description'])}</div>\r\n";
            }
        }

        return $output;
    }


    /**
     * Parses the `param` tags from a parsed docblock and extracts the parameter names and descriptions.
     *
     * @param array $parsedDocblock The parsed docblock that contains the `param` tags.
     * @return array An array of parameters, each containing 'name' and 'description'.
     */
    private function parseParameters($parsedDocblock)
    {
        $list = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'param') {
                $list[] = $tag['description'];
            }
        }
        
        $params = [];
        if (!empty($list)) {
            foreach ($list as $line) {
                $desc = trim(preg_replace('/\s\s+/', ' ', $line));
                $arr = explode(" ", $desc);
                if (count($arr) > 1 && (substr($arr[1], 0, 1) == '$' || substr($arr[1], 0, 2) == '&$')) {
                    $param = $arr[1];
                } else {
                    $param = $arr[0];
                }
                $description = substr($line, strpos($line, $param) + strlen($param) + 1);
                $params[] = [
                    'name' => $param,
                    'description' => $description
                ];
            }
        }
        return $params;
    }

    /**
     * Parses the `return` tags from a parsed docblock and extracts the return type and description.
     *
     * @param array $parsedDocblock The parsed docblock that contains the `return` tags.
     * @return array An array of return types, each containing 'type' and 'description'.
     */
    private function parseReturns($parsedDocblock)
    {
        $list = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'return') {
                $list[] = $tag['description'];
            }
        }
        $returns = [];
        if (!empty($list)) {
            foreach ($list as $line) {
                $desc = trim(preg_replace('/\s\s+/', ' ', $line));
                $arr = explode(" ", $desc);
                if (count($arr) > 1 && substr($arr[1], 0, 1) == '$') {
                    $type = $arr[1];
                } else {
                    $type = $arr[0];
                }
                $description = substr($line, strpos($line, $type) + strlen($type) + 1);
                $returns[] = [
                    'type' => $type,
                    'description' => $description
                ];
            }
        }
        return $returns;
    }

    /**
     * Parses the `throws` tags from a parsed docblock and extracts the thrown exceptions and their descriptions.
     *
     * @param array $parsedDocblock The parsed docblock that contains the `throws` tags.
     * @return array An array of thrown exceptions, each containing 'type' and 'description'.
     */
    private function parseThrows($parsedDocblock)
    {
        $list = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'throws') {
                $list[] = $tag['description'];
            }
        }
        $throws = [];
        if (!empty($list)) {
            foreach ($list as $line) {
                $desc = trim(preg_replace('/\s\s+/', ' ', $line));
                $arr = explode(" ", $desc);
                if (count($arr) > 1 && substr($arr[1], 0, 1) == '$') {
                    $type = $arr[1];
                } else {
                    $type = $arr[0];
                }
                $description = substr($line, strpos($line, $type) + strlen($type) + 1);
                $throws[] = [
                    'type' => $type,
                    'description' => $description
                ];
            }
        }
        return $throws;
    }
    
    private function getClassDeclaration($reflectionClass)
    {
        // Mendapatkan nama kelas
        $className = $reflectionClass->getName();

        // Mendapatkan nama kelas induk (parent class) jika ada
        $parentClass = $reflectionClass->getParentClass();
        $parentClassName = $parentClass ? $parentClass->getName() : null;

        // Mendapatkan semua interfaces yang diimplementasikan oleh kelas
        $interfaces = $reflectionClass->getInterfaces();
        $interfaceNames = array_map(function($interface) {
            return $interface->getName();
        }, $interfaces);

        // Menampilkan deklarasi kelas
        $basename = basename($className);
        $str = "<span class=\"php-keyword\">class</span> <span class=\"class-name\">$basename</span>";
        
        if(isset($parentClassName))
        {
            $str .= " <span class=\"php-keyword\">extends</span> <span class=\"class-name\">$parentClassName</span>";
        }
        
        if (!empty($interfaceNames)) {
            $str .= " <span class=\"php-keyword\">implements</span> <span class=\"class-name\">" . implode("</span>, <span class=\"class-name\">", $interfaceNames)."</span>";
        }
        $str .= "\r\n{\r\n";
        $str .= "}";
        
        return $str;
    }


    /**
     * Retrieves docblocks for all classes, properties, and methods in a PHP file, including access levels.
     *
     * @param string $file The PHP file to analyze.
     */
    public function getAllDocblocks($file) {
        $fileContents = file_get_contents($file);
        include_once $file;

        // Get the namespace, if any
        preg_match('/namespace\s+([a-zA-Z0-9\\\_]+)/', $fileContents, $namespaceMatches);
        $namespace = isset($namespaceMatches[1]) ? $namespaceMatches[1] : '';

        // Extract class names
        preg_match_all('/class\s+(\w+)/', $fileContents, $matches);
        $className = basename($file, '.php');
        $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

        try {
            $reflection = new ReflectionClass($fullClassName);
            $classId = str_replace("\\", "-", $fullClassName);
            echo "<div class=\"class-item\" id=\"$classId\">\n";
            echo "<h1>{$fullClassName}</h1>\n";
            
            echo "<h2>Declaration</h2>\n";
            
            $declataion = $this->getClassDeclaration($reflection);      
            
            echo "<div class=\"class-declaration\">$declataion</div>\n";
            // Class docblock
            $classDocblock = $reflection->getDocComment();
            
            if ($classDocblock) {
                $parsedClassDocblock = $this->parseDocblock($classDocblock);
                echo "<div class='docblock'>\n";
                echo $this->generateParsedDocblock($parsedClassDocblock, "h2");
                echo "</div>\n";
            }
            
            
            $constants = $reflection->getConstants();

            if (!empty($constants)) {
                echo "<h2>Constants</h2>\n";
                foreach ($constants as $name => $value) {

                    echo "<div class=\"php-constant\">";
                    echo "<span class=\"constant-name\">$name</span> = <span class=\"constant-value\">" . nl2br(htmlspecialchars(var_export($value, true)))."</span>";
                    echo "</div>";
                }
            }

            // Property docblocks with access level
            $properties = $reflection->getProperties();
            if(!empty($properties))
            {
                echo "<h2>Properties</h2>\n";
                foreach ($properties as $property) {
                    $propertyDocblock = $property->getDocComment();
                    if ($propertyDocblock) {
                        $parsedPropertyDocblock = $this->parseDocblock($propertyDocblock);
                        $accessLevel = $this->getAccessLevel($property->getModifiers());
                        $propertyType = $this->getPropertyType($parsedPropertyDocblock);
                        
                        echo "<div class='property'>\n";
                        echo "<div class=\"property-declaratiopn\"><span class=\"access-level\">{$accessLevel}</span> <span class=\"property-type\">{$propertyType}</span> <span class=\"property-name\">\${$property->getName()}</span></div>\n";
                        echo "<div class='docblock'>\n";
                        echo $this->generateParsedDocblock($parsedPropertyDocblock);
                        echo "</div>\n";
                        echo "</div>\n";
                    }
                }
            }

            // Method docblocks with access level
            $methods = $reflection->getMethods();
            if(!empty($methods))
            {
                echo "<h2>Methods</h2>\n";
                foreach ($methods as $method) {
                    $methodDocblock = $method->getDocComment();
                    if ($methodDocblock) {
                        $parsedMethodDocblock = $this->parseDocblock($methodDocblock);
                        $params = $this->getMethodParams($parsedMethodDocblock, $method);
                        $paramsStr = implode(", ", $params);

                        $returns = $this->getMethodReturns($parsedMethodDocblock);

                        if(!empty($returns))
                        {
                            $returnStr = " : ".implode(", ", $returns);
                        }
                        else
                        {
                            $returnStr = "";
                        }
                        $static = "";
                        if($method->isStatic())
                        {
                            $static = "static";
                        }

                        $accessLevel = $this->getAccessLevel($method->getModifiers());
                        echo "<div class='method'>\n";
                        echo "<div class=\"method-declaratiopn\"><span class=\"access-level\">{$accessLevel}</span> <span class=\"access-level\">{$static}</span> <span class=\"php-keyword\">function</span> <span class=\"method-name\">{$method->getName()}</span>($paramsStr)$returnStr<br>{<br>}</div>\n";
                        echo "<div class='docblock'>\n";
                        echo $this->generateParsedDocblock($parsedMethodDocblock);
                        echo "</div>\n";
                        echo "</div>\n";
                    }
                }
            }

            

            echo "</div>\n";

        } catch (ReflectionException $e) {
            echo "Could not reflect on class {$fullClassName}: " . $e->getMessage() . "<br>\n";
        }
    }

        /**
     * Retrieves the property type from the parsed docblock by searching for the `var` tag.
     *
     * @param array $parsedPropertyDocblock The parsed docblock that contains the `var` tag.
     * @return string The type of the property, or an empty string if not found.
     */
    private function getPropertyType($parsedPropertyDocblock)
    {
        $type = "";
        if (isset($parsedPropertyDocblock['tags']) && is_array($parsedPropertyDocblock['tags'])) {
            foreach ($parsedPropertyDocblock['tags'] as $tag) {
                if ($tag['tag'] == 'var') {
                    $type = trim(preg_replace('/\s\s+/', ' ', $tag['description']));
                    break;
                }
            }
        }
        return $type;
    }

    /**
     * Retrieves the default values for the parameters of a method.
     *
     * @param \ReflectionMethod $method The method whose parameters' default values are to be fetched.
     * @return array An associative array where the keys are parameter names and the values are the default values.
     */
    private function getParameterDefaults($method)
    {
        $parameters = $method->getParameters();
        $defaults = [];
    
        foreach ($parameters as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $defaults[$parameter->getName()] = $parameter->getDefaultValue();
            } else {
                $defaults[$parameter->getName()] = null;
            }
        }
    
        return $defaults;
    }

    /**
     * Parses the `param` tags from the parsed method docblock and retrieves the method parameters 
     * with their default values and HTML representation.
     *
     * @param array $parsedMethodDocblock The parsed docblock that contains the `param` tags.
     * @param \ReflectionMethod $method The method whose parameters are being parsed.
     * @return array An array of HTML strings representing the parameters with types, names, and default values.
     */
    private function getMethodParams($parsedMethodDocblock, $method)
    {
        // Get method parameters and their default values
        $defaults = $this->getParameterDefaults($method);

        $defaultValues = $this->getDefaultValues($defaults);

        $params = [];
        if (isset($parsedMethodDocblock['tags']) && is_array($parsedMethodDocblock['tags'])) {
            foreach ($parsedMethodDocblock['tags'] as $tag) {
                if ($tag['tag'] == 'param') {
                    $description = trim(preg_replace('/\s\s+/', ' ', $tag['description']));
                    $arr = explode(" ", $description);

                    if (count($arr) > 1 && substr($arr[1], 0, 1) == '$') {
                        $param = ltrim($arr[1], '$');
                        $defaultHtml = $this->getDefaultValueHtml($defaultValues, $param);
                        $paramHtml = $this->getParameterType($arr[0]) . ' <span class="parameter-name">' . $arr[1] . $defaultHtml . '</span>';
                    } else {
                        $param = ltrim($arr[0], '$');
                        $defaultHtml = $this->getDefaultValueHtml($defaultValues, $param);
                        $paramHtml = '<span class="parameter-name">' . $arr[0] . $defaultHtml . '</span>';
                    }
                    $params[] = $paramHtml;
                }
            }
        }
        return $params;
    }
    
    private function getDefaultValues($defaults)
    {
        $defaultValues = [];
        if (!empty($defaults)) {
            foreach ($defaults as $paramName => $defaultValue) {
                if ($defaultValue === null) {
                    $defaultValues[$paramName] = null;
                } else {
                    $defaultValues[$paramName] = var_export($defaultValue, true);
                }
            }
        }
        
        return $defaultValues;
    }

    /**
     * Generates the HTML for displaying the default value of a parameter, if available.
     *
     * @param array $defaultValues An associative array of parameter default values.
     * @param string $param The name of the parameter whose default value needs to be displayed.
     * @return string The HTML string for the default value, or an empty string if no default value exists.
     */
    private function getDefaultValueHtml($defaultValues, $param)
    {
        if (isset($defaultValues[$param])) {
            return ' <span class="parameter-equal-sign">=</span> <span class="parameter-default">' . $defaultValues[$param] . '</span>';
        }
        return "";
    }

    /**
     * Generates HTML to represent the type of a parameter, including handling union types.
     *
     * @param string $type The type (or types) of the parameter, possibly containing union types (separated by '|').
     * @return string The HTML string representing the parameter's type(s).
     */
    private function getParameterType($type)
    {
        $arr = explode("|", $type);
        $types = [];
        foreach ($arr as $tp) {
            $types[] = '<span class="parameter-type">' . $tp . '</span>';
        }    

        return implode("|", $types);
    }

    /**
     * Generates HTML to represent the return type of a method, including handling union types.
     *
     * @param string $type The return type (or types), possibly containing union types (separated by '|').
     * @return string The HTML string representing the return type(s).
     */
    private function getReturnType($type)
    {
        $arr = explode("|", $type);
        $types = [];
        foreach ($arr as $tp) {
            $types[] = '<span class="return-type">' . $tp . '</span>';
        }    

        return implode("|", $types);
    }

    /**
     * Parses the `return` tags from the parsed method docblock and retrieves the return type(s).
     *
     * @param array $parsedMethodDocblock The parsed docblock that contains the `return` tags.
     * @return array An array of HTML strings representing the return type(s).
     */
    private function getMethodReturns($parsedMethodDocblock)
    {
        $returns = [];
        if (isset($parsedMethodDocblock['tags']) && is_array($parsedMethodDocblock['tags'])) {
            foreach ($parsedMethodDocblock['tags'] as $tag) {
                if ($tag['tag'] == 'return') {
                    $description = trim(preg_replace('/\s\s+/', ' ', $tag['description']));
                    $arr = explode(" ", $description);
                    if (!empty($arr)) {
                        $return = $this->getReturnType($arr[0]);
                    } else {
                        $return = 'void';
                    }
                    $returns[] = $return;
                }
            }
        }
        return $returns;
    }


    /**
     * Gets the access level (public, private, protected) based on the modifiers of a property or method.
     *
     * @param int $modifiers The modifiers for the property or method.
     * @return string The access level (public, private, or protected).
     */
    private function getAccessLevel($modifiers) {
        if ($modifiers & ReflectionMethod::IS_PUBLIC) {
            return 'public';
        } elseif ($modifiers & ReflectionMethod::IS_PRIVATE) {
            return 'private';
        } elseif ($modifiers & ReflectionMethod::IS_PROTECTED) {
            return 'protected';
        }
        return 'Unknown';
    }
    
    /**
     * Scans a directory recursively for PHP files up to a certain depth.
     *
     * @param string $dir The directory to scan.
     * @param int $maxDepth The maximum depth of recursion. Default is PHP_INT_MAX.
     * @param int $currentDepth The current recursion depth. Used internally.
     * @return array Nested array representing the directory structure with PHP files.
     */
    public function scanDirectoryToc($dir, $maxDepth = PHP_INT_MAX, $currentDepth = 0) {
        $structure = [];

        // Avoid deeper scanning if depth exceeds the max limit
        if ($currentDepth > $maxDepth) {
            return $structure;
        }

        // Ensure directory is valid
        if (!is_dir($dir)) {
            return $structure;
        }

        // Scan files in the current directory first (before subdirectories)
        $files = new DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $structure[] = $this->createStdClass(
                    "file",
                    $file->getPathname(),
                    $file->getFilename()
                ); // Add file name to structure
            }
        }
        
        foreach ($files as $file) {
            if ($file->isDir() && $file->getBasename() != "." && $file->getBasename() != "..") {
                $structure[] = $this->createStdClass(
                    "dir", 
                    $file->getPathname(),
                    $file->getFilename(), 
                    $this->scanDirectoryToc($file->getPathname(), $maxDepth = PHP_INT_MAX, $currentDepth + 1)
            ); 
            }
        }

        return $structure;
    }
    
    private function createStdClass($type, $pathname, $filename, $child = null)
    {
        $result = new stdClass;
        $result->type = $type;
        $result->pathname = $pathname;
        $result->filename = $filename;
        if(isset($child))
        {
            $result->child = $child;
        }
        return $result;
    }
    
    
    // Fungsi untuk merender struktur direktori menjadi HTML
    public function renderDirectoryStructure($structure, $baseDir) {
        $html = "<ul>"; // Mulai dengan <ul>

        foreach ($structure as $item) {
            if ($item->type == "file") {
                $relativePath = "#MagicObject-".str_replace(["\\", "/", ".php"], ["-", "-", ""], $this->getRelativePath($item->pathname, $baseDir));
                // Jika item adalah file, tampilkan sebagai <li>
                $html .= "<li><a href=\"$relativePath\">" . basename($item->filename, ".php")."</a></li>";
            } elseif ($item->type == "dir") {
                // Jika item adalah subdirektori, tampilkan sebagai <li> dengan <ul> di dalamnya
                $html .= "<li>".basename($item->pathname) . "";
                // Rekursif untuk menampilkan file dan subdirektori dalam subdirektori
                if (isset($item->child) && !empty($item->child)) {
                    $html .= $this->renderDirectoryStructure($item->child, $baseDir); // Menampilkan isi subdirektori
                }
                $html .= "</li>";
            }
        }

        $html .= "</ul>"; // Tutup <ul>

        return $html;
    }

    /**
     * Generates a nested <ul> <li> HTML list for the directory structure.
     *
     * @param array $structure The nested directory structure.
     * @param string $basePath The base path for creating links.
     * @return string HTML string of the nested list.
     */
    public function generateTOC($structure, $basePath) {
        return $this->generateNestedList($structure, $basePath);
    }
    
    /**
     * Get file object
     *
     * @param mixed $value
     * @return stdClass
     */
    private function getFileObject($value)
    {
        if(isset($value[0]))
        {
            $fileObject = $value[0];
        }
        else
        {
            $fileObject = $value;
        }
        if(is_array($fileObject))
        {
            $fileObject = array_values($fileObject);
        }
        return $fileObject;
    }
    
    private function getRelativePath($pathname, $basePath) {
        // Pastikan bahwa kedua path adalah absolut
        $realPathname = realpath($pathname);
        $realBasepath = realpath($basePath);
    
        // Pastikan kedua path valid
        if ($realPathname === false || $realBasepath === false) {
            return ""; // Return false if either path is invalid
        }
    
        // Periksa apakah basepath adalah bagian dari pathname
        if (strpos($realPathname, $realBasepath) === 0) {
            // Ambil bagian setelah basepath
            return substr($realPathname, strlen($realBasepath) + 1); // +1 untuk menghapus trailing DIRECTORY_SEPARATOR
        } else {
            return ""; // Jika basepath tidak ditemukan dalam pathname
        }
    }

}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MagicObject Documentation</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>

<div class="page">

<?php

$srcDir = dirname(__DIR__) . '/src';

if (is_dir($srcDir)) {
    $docScanner = new PhpDocScanner();

    $files = $docScanner->scanDirectory($srcDir);
    
    $structure = $docScanner->scanDirectoryToc($srcDir); // Replace with your directory path
    ?>
    <div class="sidebar">
    <h3>Table of Content</h3>
    <?php
    echo $docScanner->renderDirectoryStructure($structure, $srcDir);
    ?>
    </div>
    <div class="mainbar">
    <?php
    foreach ($files as $file) {
        $docScanner->getAllDocblocks($file);
        
    }
    ?>
    </div>
    <?php
} else {
    echo "The src directory was not found. Ensure this script is run from within the project repository.\n";
}
?>
</div>
</body>
</html>
