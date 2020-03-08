<?php

namespace nigiri\themes;

use nigiri\Site;
use nigiri\views\Html;
use nigiri\views\Url;

class Theme implements ThemeInterface
{
    const PART_TITLE = 'title';
    const PART_HEAD = 'head';
    const PART_SCRIPT = 'script';
    const PART_SCRIPT_ON_READY = 'script_on_ready';
    const PART_BODY = 'body';

    protected $title = '';
    protected $head = '';
    protected $script = '';
    protected $script_on_ready = '';
    protected $body = '';

    public function append($str, $part = 'body')
    {
        if (property_exists($this, $part)) {
            $this->$part .= $str;
        }
    }

    public function resetPart($name)
    {
        if (property_exists($this, $name)) {
            $this->$name = '';
        }
    }

    public function render()
    {
        $this->title .= (empty($this->title) ? '' : ' - ') . Site::getParam(NIGIRI_PARAM_SITE_NAME);

        $ready = '';
        if (!empty($this->script_on_ready)) {
            $ready = <<<READY
<script type="application/javascript">
$(function(){
    {$this->script_on_ready}
});
</script>
READY;
        }

        echo '
<!DOCTYPE html>
<html lang="' . Site::getRouter()->getRequestedLanguage() . '">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>' . Html::escape($this->title) . '</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    
    ' . $this->head . '
  </head>
  <body>
    ' . $this->body . '

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    
    ' . $this->script . '
    
    ' . $ready . '
  </body>
</html>
';
    }
}
