<?php

namespace App\Components;

class Template
{
    private $blocks = [];
    private $templatePath = 'src/views/';
    private $cachePath = 'src/cache/';
    private $cacheEnabled = FALSE;

    public function render($file, $data = array())
    {
        $file = $this->templatePath . $file;
        $cachedFile = $this->cache($file);
        extract($data, EXTR_SKIP);

        require $cachedFile;
    }

    private function cache($file)
    {
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0744);
        }

        $cachedFile = $this->cachePath . str_replace(array('/', '.html'), array('_', ''), $file . '.php');

        if (!$this->cacheEnabled || !file_exists($cachedFile) || filemtime($cachedFile) < filemtime($file)) {
            $code = $this->includeFiles($file);
            $code = $this->compileCode($code);
            file_put_contents($cachedFile, '<?php class_exists(\''. __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code);
        }

        return $cachedFile;
    }

    public function clearCache()
    {
        foreach(glob($this->cachePath . '*') as $file) {
            unlink($file);
        }
    }

    private function compileCode($code)
    {
        $code = $this->compileBlock($code);
        $code = $this->compileYield($code);
        $code = $this->compileEscapedEchos($code);
        $code = $this->compileEchos($code);
        $code = $this->compilePHP($code);

        return $code;
    }

    private function includeFiles($file)
    {
        $code = file_get_contents($file);
        preg_match_all('/{= ?(extends|include) ?\'?(.*?)\'? ?=}/i', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $value) {
            $code = str_replace($value[0], $this->includeFiles($this->templatePath.$value[2]), $code);
        }

        $code = preg_replace('/{= ?(extends|include) ?\'?(.*?)\'? ?=}/i', '', $code);
        return $code;
    }

    private function compilePHP($code)
    {
        return preg_replace('~\{=\s*(.+?)\s*\=}~is', '<?php $1 ?>', $code);
    }

    private function compileEchos($code)
    {
        return preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $code);
    }

    private function compileEscapedEchos($code)
    {
        return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
    }

    private function compileBlock($code)
    {
        preg_match_all('/{= ?block ?(.*?) ?=}(.*?){= ?endblock ?=}/is', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $value) {
            if (!array_key_exists($value[1], $this->blocks)) $this->blocks[$value[1]] = '';

            if (strpos($value[2], '@parent') === false) {
                $this->blocks[$value[1]] = $value[2];
            } else {
                $this->blocks[$value[1]] = str_replace('@parent', $this->blocks[$value[1]], $value[2]);
            }
            $code = str_replace($value[0], '', $code);
        }
        return $code;
    }

    private function compileYield($code)
    {
        foreach ($this->blocks as $block => $value) {
            $code = preg_replace('/{= ?yield ?' . $block . ' ?=}/', $value, $code);
        }
        $code = preg_replace('/{= ?yield ?(.*?) ?=}/i', '', $code);
        return $code;
    }
}
