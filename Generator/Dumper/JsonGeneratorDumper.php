<?php

namespace Orkestra\Bundle\GuzzleBundle\Generator\Dumper;

/**
 * Json Dumper to dump service into a json file.
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class JsonGeneratorDumper
{
    /**
     * Turn commands into JSON
     *
     * @param $commands
     * @return mixed
     */
    public function dump($commands)
    {
        $dumpArray = array('types' => array(), 'commands' => array());

        foreach ($commands as $class => $command) {
            if (isset($command['Type'])) {
                foreach ($command['Type'] as $value) {
                    $name = $value->getName();

                    $dumpArray['types'][$name] = array();
                    $dumpArray['types'][$name]['class'] = $value->getClass();
                    $dumpArray['types'][$name]['pattern'] = $value->getPattern();
                }
            }
            foreach ($command['Command'] as $value) {
                $name = $value->getName();

                $dumpArray['commands'][$name] = array();

                if (isset($command['Async'])) {
                    $dumpArray['commands'][$name]['async'] = true;
                }

                $dumpArray['commands'][$name]['reference'] = $class;

                $dumpArray['commands'][$name]['uri'] = $value->getUri();

                $dumpArray['commands'][$name]['method'] = $value->getMethod();

                $dumpArray['commands'][$name]['params'] = array();

                if (isset($command['Param'])) {
                    foreach ($command['Param'] as $param) {
                        $paramArray = array();

                        $paramName = $param->getName();
                        $paramArray['type'] = $param->getType();
                        $paramArray['required'] = $param->getRequired();

                        if (null !== $param->getLocation()) {
                            $paramArray['location'] = $param->getLocation();
                        }
                        if (null !== $param->getDefault()) {
                            $paramArray['default'] = $param->getDefault();
                        }
                        if (null !== $param->getDoc()) {
                            $paramArray['doc'] = $param->getDoc();
                        }
                        if (null !== $param->getMinLength()) {
                            $paramArray['min_length'] = $param->getMinLength();
                        }
                        if (null !== $param->getMaxLength()) {
                            $paramArray['max_length'] = $param->getMaxLength();
                        }
                        if (null !== $param->getStatic()) {
                            $paramArray['static'] = $param->getStatic();
                        }
                        if (null !== $param->getPrepend()) {
                            $paramArray['prepend'] = $param->getPrepend();
                        }
                        if (null !== $param->getAppend()) {
                            $paramArray['append'] = $param->getAppend();
                        }
                        if (null !== $param->getFilters()) {
                            $paramArray['filters'] = $param->getFilters();
                        }

                        $dumpArray['commands'][$name]['params'][$paramName] = $paramArray;
                    }
                }
            }
        }

        return str_replace('\/','/',json_encode($dumpArray));

    }

}
