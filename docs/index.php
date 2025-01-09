<?php

use MagicObject\Util\PicoParsedown;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Class to scan PHP files in a directory recursively, parse docblocks, and display them.
 */
class PhpDocScanner {

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
     * Displays a parsed docblock with proper HTML formatting.
     *
     * @param array $parsedDocblock The parsed docblock to display.
     */
    public function displayParsedDocblock($parsedDocblock) {
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
            echo "<h1>Class: {$fullClassName}</h1>\n";
            
            // Class docblock
            $classDocblock = $reflection->getDocComment();
            if ($classDocblock) {
                $parsedClassDocblock = $this->parseDocblock($classDocblock);
                echo "<div class='docblock'>\n";
                echo "<strong>Class Docblock:</strong><br>\n";
                $this->displayParsedDocblock($parsedClassDocblock);
                echo "</div>\n";
            }

            // Property docblocks with access level
            if(!empty($reflection->getProperties()))
            {
                echo "<h2>Property</h2>\n";
                foreach ($reflection->getProperties() as $property) {
                    $propertyDocblock = $property->getDocComment();
                    if ($propertyDocblock) {
                        $parsedPropertyDocblock = $this->parseDocblock($propertyDocblock);
                        $accessLevel = $this->getAccessLevel($property->getModifiers());
                        echo "<div class='property'>\n";
                        echo "<div class=\"property-declaratiopn\"><span class=\"access-level\">{$accessLevel}</span> \${$property->getName()}</div>\n";
                        echo "<div class='docblock'>\n";
                        $this->displayParsedDocblock($parsedPropertyDocblock);
                        echo "</div>\n";
                        echo "</div>\n";
                    }
                }
            }

            // Method docblocks with access level
            if(!empty($reflection->getProperties()))
            {
                echo "<h2>Method</h2>\n";
                foreach ($reflection->getMethods() as $method) {
                    $methodDocblock = $method->getDocComment();
                    if ($methodDocblock) {
                        $parsedMethodDocblock = $this->parseDocblock($methodDocblock);
                        $params = $this->getMethodParams($parsedMethodDocblock);
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

                        $accessLevel = $this->getAccessLevel($method->getModifiers());
                        echo "<div class='method'>\n";
                        echo "<div class=\"method-declaratiopn\"><span class=\"access-level\">{$accessLevel}</span> <span class=\"method-name\">{$method->getName()}</span>($paramsStr)$returnStr</div>\n";
                        echo "<div class='docblock'>\n";
                        $this->displayParsedDocblock($parsedMethodDocblock);
                        echo "</div>\n";
                        echo "</div>\n";
                    }
                }
            }

        } catch (ReflectionException $e) {
            echo "Could not reflect on class {$fullClassName}: " . $e->getMessage() . "<br>\n";
        }
    }

    private function getMethodParams($parsedMethodDocblock)
    {
        $params = [];
        if(isset($parsedMethodDocblock['tags']) && is_array($parsedMethodDocblock['tags']))
        {
            foreach($parsedMethodDocblock['tags'] as $tag)
            {
                if($tag['tag'] == 'param')
                {
                    $description = trim(preg_replace('/\s\s+/', ' ', $tag['description']));
                    $arr = explode(" ", $description);
                    if(count($arr) > 1 && substr($arr[1], 0, 1) == '$')
                    {
                        $param = $this->getParameterType($arr[0]).' <span class="parameter-name">'.$arr[1].'</span>';
                    }
                    else
                    {
                        $param = '<span class="parameter-name">'.$arr[0].'</span>';
                    }
                    $params[] = $param;
                }
            }
        }
        return $params;
    }

    private function getParameterType($type)
    {
        $arr = explode("|", $type);
        $types = [];
        foreach($arr as $tp)
        {
            $types[] = '<span class="parameter-type">'.$tp.'</span>'; 
        }    

        return implode("|", $types);
    }

    private function getReturnType($type)
    {
        $arr = explode("|", $type);
        $types = [];
        foreach($arr as $tp)
        {
            $types[] = '<span class="return-type">'.$tp.'</span>'; 
        }    

        return implode("|", $types);
    }

    private function getMethodReturns($parsedMethodDocblock)
    {
        $ewrurns = [];
        if(isset($parsedMethodDocblock['tags']) && is_array($parsedMethodDocblock['tags']))
        {
            foreach($parsedMethodDocblock['tags'] as $tag)
            {
                if($tag['tag'] == 'return')
                {
                    $description = trim(preg_replace('/\s\s+/', ' ', $tag['description']));
                    $arr = explode(" ", $description);
                    if(!empty($arr))
                    {
                        $return = $this->getReturnType($arr[0]);
                    }
                    else
                    {
                        $return = 'void';
                    }
                    $ewrurns[] = $return;
                }
            }
        }
        return $ewrurns;
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

}

?>

<style>
    .method, .property
    {
        border-bottom: 1px solid #dddddd;
        margin-bottom: 10px;
    }
    .property-declaratiopn, .method-declaratiopn {
    font-family: monospace;    /* Use a monospaced font like the default <pre> */
    white-space: pre;          /* Preserve whitespace and line breaks */
    overflow: auto;            /* Ensure it scrolls if text overflows */
    line-height: 1.5;          /* Spacing between lines */
    font-size: 14px;           /* Adjust the font size */
}
pre {
    font-family: monospace;    /* Use a monospaced font like the default <pre> */
    white-space: pre;          /* Preserve whitespace and line breaks */
    background-color: #f4f4f4; /* Light gray background */
    padding: 10px;             /* Add padding to separate text from borders */
    border: 1px solid #ccc;   /* Border to give it a distinct box */
    overflow: auto;            /* Ensure it scrolls if text overflows */
    line-height: 1.5;          /* Spacing between lines */
    font-size: 14px;           /* Adjust the font size */
}
    .access-level{
        color: #708;
    }
    .method-name{
    }

    .parameter-name
    {
        color: #05a;
    }
    .parameter-type
    {
        color: #00f;
    }
    .return-type
    {
        color: #00f;
    }
</style>

<?php

// Set the source directory
$srcDir = dirname(__DIR__) . '/src'; // Adjust this as needed

// Check if the source directory exists
if (is_dir($srcDir)) {
    $docScanner = new PhpDocScanner();

    // Scan directory for PHP files
    $files = $docScanner->scanDirectory($srcDir);

    // Process each file and display docblocks
    foreach ($files as $file) {
        $docScanner->getAllDocblocks($file);
    }
} else {
    echo "The src directory was not found. Ensure this script is run from within the project repository.\n";
}
