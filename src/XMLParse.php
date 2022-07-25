<?php

namespace Rsa97\NicRu;

trait XMLParse
{
    protected static function parse(\SimpleXMLElement $source): array
    {
        $props = [
            'required' => [],
            'optional' => []
        ];
        $class = get_class();
        $ref = new \ReflectionClass($class);
        foreach($ref->getProperties() as $prop) {
        	$name = $prop->name;
        	foreach ($prop->getAttributes() as $attr) {
        		if ($attr->getName() === __TRAIT__) {
        			$args = $attr->getArguments();
        			$objName = $args[0] ?? null;
                    $isAttr = $objName[0] === '@';
                    if ($isAttr) {
                        $objName = substr($objName, 1);
                    }
                    $req = ($args['required'] ?? false) ? 'required' : 'optional';
        			// if ($objName !== null && isset($source->{$objName})) {
        				$val = $isAttr ? ($source[$objName] ?? null) : ($source->{$objName} ?? null);
                        if ($val === null) {
                            continue;
                        }
        				switch ($args['type'] ?? $prop->getType()->getName()) {
        					// case 'timezone':
                            //     if ($val !== '') {
        					// 	    $result[$propType][$name] = new \DateTimeZone($val);
                            //     }
        					// 	break;
                            // case 'timestamp_ms':
                            //     $val /= 1000;
                            // case 'timestamp':
                            //     $result[$propType][$name] = (new \DateTimeImmutable())->setTimestamp($val);
                            //     break;
                            // case 'class':
        					// 	$result[$propType][$name] = "{$nameSpace}\\{$args['class']}"::from($val);
        					// 	break;
                            // case 'array':
                            //     $result[$propType][$name] = array_map(
                            //         fn($el) => "{$nameSpace}\\{$args['class']}"::from($el),
                            //         $val
                            //     );
                            //     break;
                            case 'datetime':
                                $props[$req][$name] = new \DateTimeImmutable($val);
                                break;
                            case 'method':
                                $method = $args['method'];
                                $props[$req][$name] = static::$method($val);
                                break;
                            case 'sub':
                                $sub = static::parse($source->{$objName});
                                $props['required'] = $props['required'] + $sub['required'];
                                $props['optional'] = $props['optional'] + $sub['optional'];
                                break;
                            case 'int':
                                $props[$req][$name] = intval("{$val}");
                                break;
                            case 'bool':
                                $props[$req][$name] = ("{$val}" === 'true');
                                break;
        					default:
                                $props[$req][$name] = "{$val}";
        						break;
        				}
        			// } elseif ($objName === null) {
                    //     switch ($args['type'] ?? null) {
                    //         case 'class':
                    //             $result[$propType][$name] = "{$nameSpace}\\{$args['class']}"::from($source);
                    //             break;
                    //         case 'method':
                    //             $method = $args['method'];
                    //             $result[$propType][$name] = static::$method($source);
                    //             break;
                    //     }
                    // }
        		}
        	}
        }
        return $props;
    }

    protected function fill(\SimpleXMLElement $source): void
    {
        $props = static::parse($source);
        foreach ($props['required'] as $name => $value) {
            $this->{$name} = $value;
        }
        foreach ($props['optional'] as $name => $value) {
            $this->{$name} = $value;
        }
    }

    public static function from(\SimpleXMLElement $source): self
    {
        $props = static::parse($source);
        var_dump($props);
        $self = new static(...$props['required']);
        foreach ($props['optional'] as $name => $value) {
            $self->{$name} = $value;
        }
        return $self;
    }
}
