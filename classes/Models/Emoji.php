<?php

namespace Models;

class Emoji
{
    private $rules = [];

    public function __construct()
    {
        $rulesFileName = \RegistryConfig::getInstance()->get('Emoji.rulesFileName');

        if (empty($rulesFileName) || !is_file($rulesFileName)) {
            throw new \Exception('File with emoji rules not found.');
        }

        if (!($this->rules = json_decode(file_get_contents($rulesFileName), true))) {
            throw new \Exception('File with emoji rules is empty or bad format.');
        }
    }

    public function emojifyText($text)
    {
        $text = $this->convertSpaces($text);

        foreach ($this->rules as $keyword => $emoji) {
            $keyword = preg_quote($keyword, '~');
            $text    = preg_replace_callback(
                '~(?P<spaces1>\s)(?P<keyword>' . $keyword . ')(?P<spaces2>\s)~i',
                function ($matches) use ($emoji) {
                    $emoji = $emoji[array_rand($emoji)];
                    return $matches['spaces1'] . $matches['keyword'] . ' ' . $emoji . $matches['spaces2'];
                },
                $text
            );
        }

        return $text;
    }

    /**
     * @param $text
     * @return string
     */
    private function convertSpaces($text)
    {
        return preg_replace('/\t|&nbsp;|\x{0020}|\x{00A0}|[\x{2002}-\x{2006}]|\x{2009}|\x{200A}/u', ' ', $text);
    }

}
