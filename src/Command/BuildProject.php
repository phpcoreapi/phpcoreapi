<?php
namespace PhpCore\Command;

class BuildProject
{
    public function handle()
    {
        if (!file_exists('composer.json')) {
            echo "composer.json not found\n";
            exit;
        }

        $dist = 'dist';
        if (is_dir($dist)) $this->delete($dist);
        mkdir($dist);

        $items = ['app', 'public', 'composer.json'];

        foreach ($items as $item) {
            $this->copyRecursive($item, "$dist/$item");
        }

        chdir($dist);
        exec('composer install --no-dev --optimize-autoloader');

        echo "Production build ready in /dist\n";
    }

    private function copyRecursive($src, $dst)
    {
        if (is_dir($src)) {
            mkdir($dst, 0777, true);
            foreach (scandir($src) as $f) {
                if ($f === '.' || $f === '..') continue;
                $this->copyRecursive("$src/$f", "$dst/$f");
            }
        } else {
            copy($src, $dst);
        }
    }

    private function delete($dir)
    {
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $p = "$dir/$f";
            is_dir($p) ? $this->delete($p) : unlink($p);
        }
        rmdir($dir);
    }
}
