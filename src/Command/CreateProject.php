<?php
namespace PhpCore\Command;

class CreateProject
{
    public function handle(?string $name = null)
    {
        if (!$name) {
            echo "\e[36m?\e[0m \e[1mEnter project name:\e[0m \e[2m›\e[0m ";
            $name = trim(fgets(STDIN));
        }

        if (!$name) {
            echo "\e[31mProject name cannot be empty\e[0m\n";
            exit;
        }

        if (is_dir($name)) {
            echo "\e[31mDirectory '$name' already exists\e[0m\n";
            exit;
        }

        $options = ['normal', 'with debug panel'];
        $selectedOption = $this->select("Choose template mode:", $options);
        $template = ($selectedOption === 'with debug panel') ? 'debug-panel' : 'normal';

        $runServer = $this->select("Run the server immediately after creating?", ['yes', 'no']);

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
        echo "\n\e[36minstalling dependencies...\e[0m\n";
        exec('composer install');

        echo "\n\e[32m✔\e[0m Project \e[1m$name\e[0m created successfully with \e[36m$template\e[0m template.\n";

        if ($runServer === 'yes') {
            echo "\n\e[36mstarting server...\e[0m\n";
            passthru("phpcoreapi serve");
        } else {
            echo "  cd $name\n";
            echo "  phpcoreapi serve\n\n";
        }
    }

    private function select(string $prompt, array $options): string
    {
        $selected = 0;
        $count = count($options);
        echo "\e[?25l";
        while (true) {
            echo "\r\e[J\e[36m?\e[0m \e[1m$prompt\e[0m \e[2m(Use arrow keys)\e[0m\n";
            foreach ($options as $i => $option) {
                echo ($i === $selected ? "\e[36m❯ $option\e[0m" : "  $option") . "\n";
            }
            $key = $this->readKey();
            if ($key === 'UP')
                $selected = ($selected - 1 + $count) % $count;
            else if ($key === 'DOWN')
                $selected = ($selected + 1) % $count;
            else if ($key === 'ENTER') {
                echo "\e[" . ($count + 1) . "A\r\e[J";
                echo "\e[32m✔\e[0m \e[1m$prompt\e[0m \e[36m" . $options[$selected] . "\e[0m\n";
                echo "\e[?25h";
                return $options[$selected];
            }
            echo "\e[" . ($count + 1) . "A";
        }
    }

    private function readKey(): string
    {
        static $hasStty = null;
        if ($hasStty === null) {
            exec('stty 2>&1', $output, $exitCode);
            $hasStty = ($exitCode === 0);
        }
        if ($hasStty) {
            system('stty -icanon -echo');
            $c = fread(STDIN, 1);
            if (ord($c) === 27) {
                $c .= fread(STDIN, 2);
            }
            system('stty icanon echo');
            if ($c === "\e[A")
                return 'UP';
            if ($c === "\e[B")
                return 'DOWN';
            if ($c === "\n" || $c === "\r")
                return 'ENTER';
        } else if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "powershell -Command \"\$k=[Console]::ReadKey(\$true);if(\$k.Key -eq 'UpArrow'){'UP'}elseif(\$k.Key -eq 'DownArrow'){'DOWN'}elseif(\$k.Key -eq 'Enter'){'ENTER'}\"";
            return trim(shell_exec($cmd) ?? '');
        }
        return '';
    }
}
