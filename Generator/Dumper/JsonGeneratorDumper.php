<?php

namespace Orkestra\Bundle\GuzzleBundle\Generator\Dumper;


class JsonGeneratorDumper
{
    public function dump($commands)
    {
        $dumpArray = array('types' => array(), 'commands' => array());

        foreach ($commands as $method => $command) {

            foreach ($command['Type'] as $value) {
                $name = $value->getName();

                $dumpArray['types'][$name] = array();
                $dumpArray['types'][$name]['class'] = $value->getClass();
                $dumpArray['types'][$name]['pattern'] = $value->getPattern();
            }

            foreach ($command['Command'] as $value)
            {
                $name = $value->getName();

                $dumpArray['commands'][$name] = array();

                $dumpArray['commands'][$name]['methodName'] = $method;

                $dumpArray['commands'][$name]['uri'] = $value->getUri();

                $dumpArray['commands'][$name]['method'] = $value->getMethod();

                $dumpArray['commands'][$name]['params'] = array();

                if (isset($command['Param'])) {
                    foreach ($command['Param'] as $param) {
                        $paramArray = array();

                        $paramName = $param->getName();
                        $paramArray['type'] = $param->getType();
                        $paramArray['required'] = $param->getRequired();
                        $dumpArray['commands'][$name]['params'][$paramName] = $paramArray;
                    }
                }
            }
        }

        return str_replace('\/','/',json_encode($dumpArray));

    }

}