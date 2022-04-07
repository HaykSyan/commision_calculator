<?php
namespace App;

class Reader {
    private array $file;

    function __construct($fileName){
        if(!file_exists($fileName)){
            echo "file not found";
            exit;
        }

        $this->file = file($fileName);
    }

    public function load(): array
    {
        $data = [];
        foreach ($this->file as $line) {
            $data[] = str_getcsv($line);
        }

        return $data;
    }
}