<?php

namespace Orkestra\Bundle\GuzzleBundle\DataMapper;

use Doctrine\Common\Util;

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
                $setter = 'set'.Util\Inflector::camelize($key);
                if ($reflection->hasMethod($setter)) {
                    if (!$reflection->getMethod($setter)->isPublic()) {
                        throw new \ReflectionException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflection->name));
                    }
                    
                    $properties = $reflection->getMethod($setter)->getParameters();

                    if (!is_null($property = $properties[0]->getClass())) {
                        $o = $property->newInstance();
                        $this->bind($o, $value);
                        $value = $o;
                    }

                    $entity->$setter($value);
                } else {
                    $setter = 'add'.preg_replace('/s$/i', '', Util\Inflector::camelize($key));
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
}
