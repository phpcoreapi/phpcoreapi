<?php
namespace PhpCore\Command;

class MakeController
{
    public function handle(string $name)
    {
        $parts = explode('/',$name);
        $ctrl = array_pop($parts);
        $path = 'app/Controllers/' . implode('/',$parts);
        if($path && !is_dir($path)) mkdir($path,0777,true);
        $ns = 'App\\Controllers' . ($parts ? '\\'.implode('\\',$parts) : '');
        $file = "$path/$ctrl.php";
        $content = "<?php\n\nnamespace $ns;\n\nuse App\Core\Database;\nuse App\Core\Request;\n\nclass $ctrl\n{\n    private \$_db;\n\n    public function __construct(){\n        \$this->_db = Database::connect();\n    }\n\n    public function index(){}\n}\n";
        file_put_contents($file,$content);
        echo "Controller created at $file\n";
    }
}
