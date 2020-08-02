<?php


namespace nigiri\themes;


use nigiri\exceptions\InternalServerError;
use nigiri\Site;
use nigiri\views\LayoutData;

class LayoutTheme extends Theme
{
    protected $layoutPath;

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
                throw new InternalServerError(l("Theme Error"),
                  l("The layout file for the theme was not found in the automatic paths"));
            }
        }
    }

    public function render()
    {
        echo page_include($this->layoutPath, ['layoutData' => $this->setupLayoutData()]);
    }

    /**
     * @return LayoutData
     */
    protected function setupLayoutData()
    {
        $data = new LayoutData();
        $data->site_name = Site::getParam(NIGIRI_PARAM_SITE_NAME);
        $data->language = Site::getRouter()->getRequestedLanguage();
        $data->title = $this->title;
        $data->head = $this->head;
        $data->body = $this->body;
        $data->script = $this->script;
        $data->script_on_ready = $this->script_on_ready;

        return $data;
    }
}