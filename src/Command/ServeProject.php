<?php
namespace PhpCore\Command;

class ServeProject
{
    public function handle(int $port)
    {
        if (!is_dir('public')) {
            echo "\e[31mpublic folder not found\e[0m\n";
            exit;
        }

        $originalPort = $port;
        while (!$this->isPortAvailable($port)) {
            $port++;
        }

        if ($port !== $originalPort) {
            echo "\e[33m! Port $originalPort was busy, using port $port instead.\e[0m\n";
        }

        echo "\e[32m➜\e[0m Local: \e[1;36mhttp://localhost:$port\e[0m\n";
        passthru("php -S localhost:$port -t public");
    }

    private function isPortAvailable(int $port): bool
    {
        $connection = @fsockopen('localhost', $port);
        if (is_resource($connection)) {
            fclose($connection);
            return false;
        }
        return true;
    }
}
