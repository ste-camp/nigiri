<?php


namespace nigiri\themes;


use nigiri\exceptions\InternalServerError;
use nigiri\Site;
use nigiri\views\LayoutData;

class LayoutTheme extends Theme
{
    private $layoutPath;

    /**
     * LayoutTheme constructor.
     * @param string $layout the file containing the layout (absolute or relative to /views folder). It gets passed a LayoutData object as $layoutData variable. If empty, layout.php will be searched into /views folder or fallback to the nigiri default layout
     * @throws InternalServerError
     */
    public function __construct($layout = '')
    {
        if (!empty($layout)) {
            $pathsToCheck = [//Places to look for specified layout file, order matters!
              $layout,
              dirname(dirname(__DIR__)) . '/views/' . $layout
            ];
            foreach ($pathsToCheck as $path) {
                if (file_exists($path)) {
                    $this->layoutPath = $path;
                    break;
                }
            }

            if (empty($this->layoutPath)) {//if not found, fallback to default layout
                $layout = '';
            }
        }

        if (empty($layout)) {
            $pathsToCheck = [//Places to automatically look for layout file, order matters!
              dirname(dirname(__DIR__)) . '/views/layout.php',
              dirname(__DIR__) . '/views/layout.php'
            ];

            foreach ($pathsToCheck as $path) {
                if (file_exists($path)) {
                    $this->layoutPath = $path;
                    break;
                }
            }

            if (empty($this->layoutPath)) {//No file found :(
                throw new InternalServerError("Errore nel tema",
                  "Il layout per il tema non è stato trovato nei percorsi automatici");
            }
        }
    }

    public function render()
    {
        $data = new LayoutData();
        $data->site_name = Site::getParam('site_name');
        $data->language = Site::getRouter()->getRequestedLanguage();
        $data->title = $this->title;
        $data->head = $this->head;
        $data->body = $this->body;
        $data->script = $this->script;
        $data->script_on_ready = $this->script_on_ready;

        echo page_include($this->layoutPath, ['layoutData' => $data]);
    }

}