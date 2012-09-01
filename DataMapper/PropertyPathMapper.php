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
    public function bind($entity, array $data)
    {
        if (is_object($entity)) {
            $reflection = new \ReflectionClass($entity);

            foreach ($data as $key => $value) {
                $setter = 'set'.$this->camelize($key);
                if ($reflection->hasMethod($setter)) {
                    if (!$reflection->getMethod($setter)->isPublic()) {
                        throw new \ReflectionException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflection->name));
                    }

                    $entity->$setter($value);
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
