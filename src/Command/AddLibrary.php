<?php
namespace PhpCore\Command;

class AddLibrary
{
    public function handle(string $lib)
    {
        if(!file_exists('composer.json')){ echo "composer.json not found\n"; exit; }
        passthru("composer require $lib");
        echo "Library '$lib' added successfully.\n";
    }
}
