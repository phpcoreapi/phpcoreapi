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

        $options = ['normal', 'with debug panel'];
        $selectedOption = $this->select("Choose template mode:", $options);
        $template = ($selectedOption === 'with debug panel') ? 'debug-panel' : 'normal';

        $dirs = ['app/Controllers', 'app/Core', 'app/Routes', 'public'];
        if ($template === 'debug-panel') {
            $dirs[] = 'app/Views';
        }

        foreach ($dirs as $d) {
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

        if ($template === 'debug-panel') {
            $files[] = 'ErrorHandler.php';
            $files[] = 'error_page.php';
        }

        foreach ($files as $f) {
            $stub = __DIR__ . '/../../templates/' . $template . '/' . $f . '.stub';
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
                case 'ErrorHandler.php':
                    $dest = "$name/app/Core/ErrorHandler.php";
                    break;
                case 'error_page.php':
                    $dest = "$name/app/Views/error_page.php";
                    break;
                case 'phpcoreapi_setup.md':
                    $dest = "$name/README.md";
                    break;
                default:
                    $dest = "$name/$f";
                    break;
            }

            if (file_exists($stub)) {
                copy($stub, $dest);
            }
        }

        chdir($name);
        exec('composer install');

        echo "\nProject '$name' created with '$template' template. Set web root to /public\n";
    }

    private function select(string $prompt, array $options): string
    {
        $selected = 0;
        $count = count($options);

        // Hide cursor
        echo "\e[?25l";

        $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $hasStty = false;
        if (!$isWin) {
            exec('stty 2>&1', $output, $exitCode);
            $hasStty = ($exitCode === 0);
        }

        while (true) {
            echo "\r\e[K$prompt ";
            foreach ($options as $i => $option) {
                if ($i === $selected) {
                    echo "\e[1;32m> $option\e[0m  ";
                } else {
                    echo "  $option  ";
                }
            }

            if ($hasStty) {
                system('stty -icanon -echo');
                $key = ord(fread(STDIN, 1));
                if ($key === 27) {
                    $next = ord(fread(STDIN, 1));
                    if ($next === 91) {
                        $dir = ord(fread(STDIN, 1));
                        if ($dir === 68 || $dir === 65) { // Left/Up
                            $selected = ($selected - 1 + $count) % $count;
                        } elseif ($dir === 67 || $dir === 66) { // Right/Down
                            $selected = ($selected + 1) % $count;
                        }
                    }
                } elseif ($key === 10) {
                    system('stty icanon echo');
                    echo "\n";
                    break;
                }
                system('stty icanon echo');
            } else {
                // Fallback for Windows or systems without stty
                echo "\n(Use 1 and 2 to switch, Enter to select current): ";
                $input = trim(fgets(STDIN));
                if ($input === '1') {
                    $selected = 0;
                } elseif ($input === '2') {
                    $selected = 1;
                } elseif ($input === '') {
                    echo "\n";
                    break;
                }
                // Clear lines
                echo "\e[A\e[K\e[A\e[K";
            }
        }

        // Show cursor
        echo "\e[?25h";

        return $options[$selected];
    }
}
