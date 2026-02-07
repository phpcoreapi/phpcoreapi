<?php
namespace PhpCore\Command;

class CreateProject
{
    public function handle(?string $name = null)
    {
        if (!$name) {
            echo "Enter project name: ";
            $name = trim(fgets(STDIN));
        }

        if (!$name) {
            echo "Project name cannot be empty\n";
            exit;
        }

        if (is_dir($name)) {
            echo "Directory already exists\n";
            exit;
        }

        foreach (['app/Controllers', 'app/Core', 'app/Routes', 'public'] as $d) {
            mkdir("$name/$d", 0777, true);
        }

        $vendor = "phpcoreapi";
        $package = strtolower($name);
        $composerJson = [
            "name" => "$vendor/$package",
            "description" => "A PHP Core API project",
            "type" => "project",
            "require" => [
                "php" => ">=7.4"
            ],
            "autoload" => [
                "psr-4" => [
                    "App\\" => "app/"
                ]
            ]
        ];
        file_put_contents("$name/composer.json", json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $files = [
            'index.php',
            'web.php',
            'HealthController.php',
            'Database.php',
            'Request.php',
            'Router.php',
            'phpcoreapi_setup.md'
        ];

        foreach ($files as $f) {
            $stub = __DIR__ . '/../../templates/' . $f . '.stub';
            switch ($f) {
                case 'index.php':
                    $dest = "$name/public/index.php";
                    break;
                case 'web.php':
                    $dest = "$name/app/Routes/web.php";
                    break;
                case 'HealthController.php':
                    $dest = "$name/app/Controllers/HealthController.php";
                    break;
                case 'Database.php':
                    $dest = "$name/app/Core/Database.php";
                    break;
                case 'Request.php':
                    $dest = "$name/app/Core/Request.php";
                    break;
                case 'Router.php':
                    $dest = "$name/app/Core/Router.php";
                    break;
                case 'phpcoreapi_setup.md':
                    $dest = "$name/README.md";
                    break;
                default:
                    $dest = "$name/$f";
                    break;
            }

            copy($stub, $dest);
        }

        chdir($name);
        exec('composer install');

        echo "Project '$name' created. Set web root to /public\n";
    }
}
