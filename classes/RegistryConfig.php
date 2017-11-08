<?php

/**
 * Loading params:
 *      1. {absPath}/{pathsToParams}/{paramsFileName}
 *      2. {absPath}/{pathsToParams}/{pathDomains}/{workDomain}/{paramsFileName}
 *          ...
 *
 * version 2
 *      + multidomain
 *      - parameter 'ABSPATH'replaced to ->absPath()
 * version 3
 *      - change extension file name for ParamsFromFile from .php to params.{ParamName}.php
 * version 3
 *      - restructured folders
 * @version 4.1
 */
class RegistryConfig
{

    private        $values          = [];
    private        $absPath         = '';
    private        $workDomain      = '';
    private        $alternateDomain = '';
    static private $instance        = null;
    /*
     * Path to parameters
     */
    private $pathsToParams     = [
        'publicParams'  => 'params/',
        'privateParams' => 'params/private/',
        'debugParams'   => 'paramsDebug/',
    ];
    private $pathDomains       = 'domains/';
    private $paramsFileName    = 'params.php';
    private $afterLoadCallback = null;

    /**
     * @param string $workDomain
     * @param boolean $recreate
     * @return RegistryConfig
     */
    static public function getInstance($workDomain = null, $recreate = false)
    {
        if (is_null(self::$instance) || $recreate) {
            self::$instance = new self($workDomain);
        }
        return self::$instance;
    }

    private function __clone()
    {

    }

    private function __wakeup()
    {

    }

    /**
     *
     * @return array
     */
    public function getAllDomainsName()
    {
        $result = [];

        foreach ($this->pathsToParams as $path) {
            $path = $this->absPath . $path . $this->pathDomains;
            if (!is_dir($path)) {
                continue;
            }

            foreach (
                array_filter(
                    scandir($path),
                    function ($item) {
                        return ($item != '.') && ($item != '..');
                    }
                ) as $domain) {
                $result[$domain] = $domain;
            }
        }

        return array_values($result);
    }

    private function __construct($workDomain = null)
    {
        if (!defined('MY_ABSPATH')) {
            throw new Exception('MY_ABSPATH is not defined.');
        }

        $this->absPath = rtrim(MY_ABSPATH, '/ ') . '/';
        if (!is_null($workDomain)) {
            $this->workDomain = $workDomain;
        }
        else {
            $this->workDomain =
                !empty($_SERVER['HTTP_X_CONFIG_DOMAIN'])
                    ? str_replace('www.', '', strtolower($_SERVER['HTTP_X_CONFIG_DOMAIN']))
                    :
                    (!empty($_SERVER['HTTP_HOST'])
                        ? str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']))
                        : ''
                    );
        }

        $this->loadParams();

        if (!is_null($this->afterLoadCallback)) {
            call_user_func($this->afterLoadCallback);
        }
    }

    /**
     * Load parameters from file
     * @param string $fileName
     * @param string $paramName
     */
    private function loadParams($fileName = '', $paramName = '')
    {
        if (empty($fileName)) {
            $fileName = $this->paramsFileName;
        }

        foreach ($this->pathsToParams as $path) {
            $path = $this->absPath . $path;

            // Loading of base parameters
            $this->paramsMerge($this->values, $this->loadParamsFromFile(
                $path . $fileName, $paramName
            ));

            // Loading of alternate domain parameters fo debug
            if (!empty($this->alternateDomain)) {
                $this->paramsMerge($this->values, $this->loadParamsFromFile(
                    $path . $this->pathDomains . $this->alternateDomain . '/' . $fileName, $paramName
                ));
            }
            // Loading of domain parameters
            if (!empty($this->workDomain)) {
                $this->paramsMerge($this->values, $this->loadParamsFromFile(
                    $path . $this->pathDomains . $this->workDomain . '/' . $fileName, $paramName
                ));
            }
        }
    }

    /**
     * Safe merge of parameters
     *
     * @param array $array1
     * @param array $array2
     */
    private function paramsMerge(&$array1, $array2)
    {
        if (empty($array2) || !is_array($array2)) {
            return;
        }

        foreach ($array2 as $key => $value) {
            if (!is_array($value)) {
                $array1[$key] = $value;
                if (is_null($value)) {
                    unset($array1[$key]);
                }
            }
            else {
                if (!isset($array1[$key])) {
                    $array1[$key] = array();
                }
                $this->paramsMerge($array1[$key], $value);
            }
        }
    }

    /**
     * Load params from file
     *
     * @param string $paramFileName
     * @return array|mixed
     */
    private function loadParamsFromFile($paramFileName, $paramName = '')
    {
        if (file_exists($paramFileName)) {
            $params = include $paramFileName;
            return empty($paramName) ? $params : [$paramName => $params];
        }
        else {
            return [];
        }
    }

    /**
     * Check parameter file with the name equal to name of the parameter.
     * If found - load parameters
     * @param string $paramName
     * @return boolean true - if params loaded
     */
    private function checkParamFile($paramName)
    {
        $this->loadParams('params.' . $paramName . '.php', $paramName);
        return (isset($this->values[$paramName]));
    }

    /**
     * @return string
     */
    public function absPath()
    {
        return $this->absPath;
    }

    /**
     * @return string
     */
    public function workDomain()
    {
        return $this->workDomain;
    }

    /**
     * @param string|array $keys Param name | chain of parameter names
     * @return boolean
     */
    public function isEmpty($keys)
    {
        $value = $this->get($keys, false);
        return empty($value);
    }

    /**
     * @param string|array $keys Param name | chain of parameter names
     * @param mixed $default - is null throw if parameter not set
     * @return mixed
     * @throws Exception
     */
    public function get($keys, $default = null)
    {
        if ($keys == '*') {
            return $this->values;
        }

        if (!is_array($keys)) {
            $keys = explode('.', $keys);
        }

        $value = &$this->values;
        $key_s = '';
        $step  = 0;

        foreach ($keys as $key) {
            $step++;
            $key_s .= '[' . $key . ']';
            if (!isset($value[$key])) {
                if (($step != 1) || (($step == 1) && !$this->checkParamFile($key))) {
                    if (is_null($default)) {
                        throw new Exception(sprintf('Key %s not found', $key_s));
                    }
                    else {
                        return $default;
                    }
                }
            }
            $value = &$value[$key];
        }

        return $value;
    }

    /**
     * @param string|array $keys Param name | chain of parameter names
     * @param mixed $value
     */
    public function set($keys, $value)
    {
        if (!is_array($keys)) {
            $keys = explode('.', $keys);
        }

        $pointer = &$this->values;
        $step    = count($keys);

        foreach ($keys as $key) {
            $step--;
            if (!isset($pointer[$key]) && ($step != 0)) {
                $pointer[$key] = array();
            }

            if (empty($step)) {
                $pointer[$key] = $value;
            }
            else {
                $pointer = &$pointer[$key];
            }
        }
    }

}
