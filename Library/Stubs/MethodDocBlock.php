<?php

/*
 +--------------------------------------------------------------------------+
 | Zephir Language                                                          |
 +--------------------------------------------------------------------------+
 | Copyright (c) 2013-2014 Zephir Team and contributors                     |
 +--------------------------------------------------------------------------+
 | This source file is subject the MIT license, that is bundled with        |
 | this package in the file LICENSE, and is available through the           |
 | world-wide-web at the following url:                                     |
 | http://zephir-lang.com/license.html                                      |
 |                                                                          |
 | If you did not receive a copy of the MIT license and are unable          |
 | to obtain it through the world-wide-web, please send a note to           |
 | license@zephir-lang.com so we can mail you a copy immediately.           |
 +--------------------------------------------------------------------------+
*/

namespace Zephir\Stubs;

use Zephir\ClassMethod;

/**
 * Stubs Generator
 */
class MethodDocBlock extends DocBlock
{
    private $parameters = array();

    private $return;

    public function __construct(ClassMethod $method, $indent = 4)
    {
        parent::__construct($method->getDocBlock(), $indent);
        $this->parseMethodParameters($method);
        $this->parseLines();
        if (!$this->return) {
            $this->parseMethodReturnType($method);
        }
        if ($this->parameters) {
            $this->appendParametersLines();
        }
        if ($this->return) {
            $this->appendReturnLine();
        }
    }

    protected function parseMethodReturnType(ClassMethod $method)
    {
        $return = array();
        $returnTypes = $method->getReturnTypes();
        if ($returnTypes) {
            foreach ($returnTypes as $type) {
                if (isset($type['data-type'])) {
                    $return[] = $type['data-type'];
                }
            }
        }
        $returnClassTypes = $method->getReturnClassTypes();
        if ($returnClassTypes) {
            $return = array_merge($return, $returnClassTypes);
        }
        if ($return) {
            $this->return = array(join('|', $return), '');
        }
    }

    protected function parseLines()
    {
        $lines = array();

        foreach ($this->lines as $line) {
            if (preg_match('#@(param|return) *(\w+)* *(\$\w+)* *(.+?)*#', $line, $matches) === 0) {
                $lines[] = $line;
            } else {
                list(, $docType, $type, $name, $description) = $matches;
                switch ($docType) {
                    case 'param':
                        $this->parameters[$name] = array($type, $description);
                        break;
                    case 'return':
                        $this->return = array($type, $description);
                        break;
                }
            }
        }
        $this->lines = $lines;
    }

    private function appendReturnLine()
    {
        list($type, $description) = $this->return;
        $this->lines[] = '@return ' . $type . ' ' . $description;
    }

    private function parseMethodParameters(ClassMethod $method)
    {
        $parameters = $method->getParameters();
        if (!$parameters) {
            return;
        }
        foreach ($method->getParameters() as $parameter) {
            if (isset($parameter['data-type'])) {
                if ($parameter['data-type'] == 'variable') {
                    $type = 'mixed';
                } else {
                    $type = $parameter['data-type'];
                }
            } elseif (isset($parameter['cast'])) {
                $type = $parameter['cast']['value'];
            } else {
                $type = 'mixed';
            }
            $this->parameters['$' . $parameter['name']] = array($type, '');
        }
    }

    private function appendParametersLines()
    {
        foreach ($this->parameters as $name => $parameter) {
            list($type, $description) = $parameter;
            $this->lines[] = '@param ' . $type . ' ' . $name . ' ' . $description;
        }
    }
}
