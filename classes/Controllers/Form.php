<?php

namespace Controllers;

class Form
{
    private $templateForm = 'Templates/Form.php';

    public function processRequest()
    {
        $errorMsg = '';
        $result   = '';
        $source   = isset($_POST['source']) ? $_POST['source'] : '';
        if (!empty($source)) {
            try {
                $result = (new \Models\Emoji())->emojifyText($source);
            }
            catch (\Exception $e) {
                $errorMsg = $e->getMessage();
            }
        }

        echo $this->render($this->templateForm, [
            'source'   => $source,
            'result'   => $result,
            'errorMsg' => $errorMsg,
        ]);
    }

    public function render($viewTemplateFile, $vars)
    {
        if (array_key_exists('viewTemplateFile', $vars)) {
            throw new \Exception('Cannot bind variable called "viewTemplateFile"');
        }
        extract($vars);
        ob_start();
        require $viewTemplateFile;
        return ob_get_clean();
    }
}
