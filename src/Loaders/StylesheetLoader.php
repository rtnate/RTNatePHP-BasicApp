<?php 

namespace RTNatePHP\BasicApp\Loaders;

class StylesheetLoader 
{
    protected $sheets = [];

    public function __construct(string $filename)
    {
        $jsonLoader = new JsonLoader($filename);
        if ($jsonLoader->valid())
        {
            $this->loadSheets($jsonLoader->data());
        }
    }

    public function sheetsGlobal(): array
    {
        return $this->filterSheets();
    }

    public function sheetsPage(string $page): array
    {
        return $this->filterSheets($page);
    }

    protected function loadSheets($sheets)
    {
        if (!is_array($sheets)) return;
        foreach($sheets as $key => $item)
        {
            if (!is_int($key))
            {
                $this->loadSheetsWithKey($key, $item);
            }
            else 
            {
                $this->loadSheetsWithoutKey($item);
            }
        }
    }

    protected function loadSheetsWithKey($key, $sheets)
    {
        if (is_array($sheets))
        {
            foreach($sheets as $sheet)
            {
                $sheet['page'] = $key;
                array_push($this->sheets, $sheet);
            }
        }
        else 
        {
            $sheets['page'] = $key;
            array_push($this->sheets, $sheets);
        }
    }

    protected function loadSheetsWithoutKey($sheets)
    {
        if (is_array($sheets))
        {
            foreach($sheets as $sheet)
            {
                if (!array_key_exists('page', $sheet)) $sheet['page'] = 'global';
                array_push($this->sheets, $sheet);
            }
        }
        else 
        {
            if (!array_key_exists('page', $sheets)) $sheets['page'] = 'global';
            array_push($this->sheets, $sheets);
        }
    }

    protected function filterSheets(string $page = ''): array
    {
        if (!$page) $page = 'global';
        $results = [];
        foreach($this->sheets as $sheet)
        {
            if (array_key_exists('page', $sheet))
            {
                if ($sheet['page'] == $page || $sheet['page'] == 'global')
                {
                    array_push($results, $sheet);
                }
            }
            else array_push($results, $sheet);
        }
        return $results;
    }
}