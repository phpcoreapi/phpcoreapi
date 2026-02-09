<?php
namespace PhpCore\Command;

class ServeProject
{
    public function handle(int $port)
    {
        if (!is_dir('public')) {
            echo "public folder not found\n";
            exit;
        }
        echo "\e[1;32m➜\e[0m Local: \e[1;36mhttp://localhost:$port\e[0m\n";
        passthru("php -S localhost:$port -t public");
    }
}
