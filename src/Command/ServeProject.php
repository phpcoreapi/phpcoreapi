<?php
namespace PhpCore\Command;

class ServeProject
{
    public function handle(int $port)
    {
        if(!is_dir('public')){ echo "public folder not found\n"; exit; }
        echo "Server running at http://localhost:$port\n";
        passthru("php -S localhost:$port -t public");
    }
}
