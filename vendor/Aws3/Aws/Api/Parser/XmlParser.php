<?php

namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Parser;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\DateTimeResult;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\ListShape;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\MapShape;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Parser\Exception\ParserException;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\StructureShape;
/**
 * @internal Implements standard XML parsing for REST-XML and Query protocols.
 */
class XmlParser
{
    public function parse(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\StructureShape $shape, \SimpleXMLElement $value)
    {
        return $this->dispatch($shape, $value);
    }
    private function dispatch($shape, \SimpleXMLElement $value)
    {
        static $methods = ['structure' => 'parse_structure', 'list' => 'parse_list', 'map' => 'parse_map', 'blob' => 'parse_blob', 'boolean' => 'parse_boolean', 'integer' => 'parse_integer', 'float' => 'parse_float', 'double' => 'parse_float', 'timestamp' => 'parse_timestamp'];
        $type = $shape['type'];
        if (isset($methods[$type])) {
            return $this->{$methods[$type]}($shape, $value);
        }
        return (string) $value;
    }
    private function parse_structure(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\StructureShape $shape, \SimpleXMLElement $value)
    {
        $target = [];
        foreach ($shape->getMembers() as $name => $member) {
            // Extract the name of the XML node
            $node = $this->memberKey($member, $name);
            if (isset($value->{$node})) {
                $target[$name] = $this->dispatch($member, $value->{$node});
            } else {
                $memberShape = $shape->getMember($name);
                if (!empty($memberShape['xmlAttribute'])) {
                    $target[$name] = $this->parse_xml_attribute($shape, $memberShape, $value);
                }
            }
        }
        return $target;
    }
    private function memberKey(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $shape, $name)
    {
        if (null !== $shape['locationName']) {
            return $shape['locationName'];
        }
        if ($shape instanceof ListShape && $shape['flattened']) {
            return $shape->getMember()['locationName'] ?: $name;
        }
        return $name;
    }
    private function parse_list(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\ListShape $shape, \SimpleXMLElement $value)
    {
        $target = [];
        $member = $shape->getMember();
        if (!$shape['flattened']) {
            $value = $value->{$member['locationName'] ?: 'member'};
        }
        foreach ($value as $v) {
            $target[] = $this->dispatch($member, $v);
        }
        return $target;
    }
    private function parse_map(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\MapShape $shape, \SimpleXMLElement $value)
    {
        $target = [];
        if (!$shape['flattened']) {
            $value = $value->entry;
        }
        $mapKey = $shape->getKey();
        $mapValue = $shape->getValue();
        $keyName = $shape->getKey()['locationName'] ?: 'key';
        $valueName = $shape->getValue()['locationName'] ?: 'value';
        foreach ($value as $node) {
            $key = $this->dispatch($mapKey, $node->{$keyName});
            $value = $this->dispatch($mapValue, $node->{$valueName});
            $target[$key] = $value;
        }
        return $target;
    }
    private function parse_blob(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $shape, $value)
    {
        return base64_decode((string) $value);
    }
    private function parse_float(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $shape, $value)
    {
        return (double) (string) $value;
    }
    private function parse_integer(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $shape, $value)
    {
        return (int) (string) $value;
    }
    private function parse_boolean(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $shape, $value)
    {
        return $value == 'true';
    }
    private function parse_timestamp(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $shape, $value)
    {
        if (is_string($value) || is_int($value) || is_object($value) && method_exists($value, '__toString')) {
            return \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\DateTimeResult::fromTimestamp((string) $value, !empty($shape['timestampFormat']) ? $shape['timestampFormat'] : null);
        }
        throw new \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Parser\Exception\ParserException('Invalid timestamp value passed to XmlParser::parse_timestamp');
    }
    private function parse_xml_attribute(\DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $shape, \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Shape $memberShape, $value)
    {
        $namespace = $shape['xmlNamespace']['uri'] ? $shape['xmlNamespace']['uri'] : '';
        $prefix = $shape['xmlNamespace']['prefix'] ? $shape['xmlNamespace']['prefix'] : '';
        if (!empty($prefix)) {
            $prefix .= ':';
        }
        $key = str_replace($prefix, '', $memberShape['locationName']);
        $attributes = $value->attributes($namespace);
        return isset($attributes[$key]) ? (string) $attributes[$key] : null;
    }
}
