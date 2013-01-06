<?php

namespace Orkestra\Bundle\GuzzleBundle\DataMapper;

/**
 * PropertyPathMapper Class
 *
 * Binds data to entities
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class PropertyPathMapper
{
    private $errors = array();

    /**
     * @param  Object    $entity Entity to bind data
     * @param  array     $data   Array of data to bind to object
     * @return boolean
     * @throws Exception
     */
    public function bind($entity, array &$data)
    {
        if (is_object($entity)) {
            $reflection = new \ReflectionClass($entity);

            foreach ($data as $key => $value) {
                $setter = 'set'.$this->camelize($key);
                if ($reflection->hasMethod($setter)) {
                    if (!$reflection->getMethod($setter)->isPublic()) {
                        throw new \ReflectionException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflection->name));
                    }
                    try {
                        $entity->$setter($value);
                    } catch (\Exception $e) {
                        $properties = $reflection->getMethod($setter)->getParameters();
                        $property = $properties[0]->getClass();
                        $o = $property->newInstance();
                        $this->bind($o, $value);
                        $entity->$setter($o);
                    }
                } else {
                    $setter = 'add'.preg_replace('/s$/i', '', $this->camelize($key));
                    if ($reflection->hasMethod($setter)) {
                        $method = $reflection->getMethod($setter);
                        if (!$method->isPublic()) {
                            throw new \ReflectionException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflection->name));
                        }
                        if (!is_array($value)) {
                            $value = array($value);
                        }

                        $properties = $method->getParameters();
                        $property = $properties[0]->getClass();

                        foreach ($value as $v) {
                            $o = $property->newInstance();
                            $this->bind($o, $v);
                            $entity->$setter($o);
                        }
                    }
                }
            }
        } else {
            throw new \InvalidArgumentException('Entity object must be passed');
        }
    }

    /**
     * Camelizes a given string.
     *
     * @param string $string Some string.
     *
     * @return string The camelized version of the string.
     */
    protected function camelize($string)
    {
        return preg_replace_callback('/(^|_|\.)+(.)/', function ($match) { return ('.' === $match[1] ? '_' : '').strtoupper($match[2]); }, $string);
    }
}
