<?php
namespace Wesleywmd\Invent\Model\Component;

use Wesleywmd\Invent\Api\RendererInterface;
use Wesleywmd\Invent\Model\XmlParser\Dom;
use Wesleywmd\Invent\Model\XmlParser\Location;

abstract class BaseDiXml extends AbstractXmlRenderer implements RendererInterface
{
    protected function getType()
    {
        return Location::TYPE_DI;
    }

    protected function addType(Dom &$dom, $name, $arguments = [])
    {
        $dom->updateElement('type', 'name', $name, null);
        $xpath = ['type[@name="'.$name.'"]'];
        if (!empty($arguments)) {
            $dom->updateElement('arguments', null, null, null, $xpath);
            $this->addArguments($dom, $arguments, array_merge($xpath, ['arguments']), 'argument');
        }
        return $xpath;
    }

    protected function addVirtualType(Dom &$dom, $name, $type, $arguments = [])
    {
        $dom->updateElement('virtualType', 'name', $name, null);
        $xpath = ['virtualType[@name="'.$name.'"]'];
        $dom->updateAttribute('xsi:type', $type, $xpath);
        if (!empty($arguments)) {
            $dom->updateElement('arguments', null, null, null, $xpath);
            $this->addArguments($dom, $arguments, array_merge($xpath, ['arguments']), 'argument');
        }
        return $xpath;
    }

    protected function addPlugin(Dom &$dom, $name, $plugin, $type)
    {
        $xpathType = $dom->node('type', ['name'=>$name]);
        $dom->node('plugin', ['name'=>$plugin, 'type'=>$type], null, $xpathType);
    }

    protected function addPreference(Dom &$dom, $for, $type)
    {
        $dom->updateElement('preference', 'for', $for)
            ->updateAttribute('type', $type, ['preference[@for="'.$for.'"]']);
    }

    private function addArguments(Dom &$dom, $arguments, $xpath, $tag)
    {
        foreach ($arguments as $name=>$value) {
            if (count(explode('\\', $value)) > 1) {
                return $this->addArgument($dom, $name, 'object', $value, $xpath, $tag);
            }
            if (count(explode('::', $value)) > 1) {
                return $this->addArgument($dom, $name, 'const', $value, $xpath, $tag);
            }
            if (is_string($value)) {
                return $this->addArgument($dom, $name, 'string', $value, $xpath, $tag);
            }
            if (is_bool($value)) {
                return $this->addArgument($dom, $name, 'boolean', $value*1, $xpath, $tag);
            }
            if (is_numeric($value)) {
                return $this->addArgument($dom, $name, 'number', $value, $xpath, $tag);
            }
            if (is_null($value)) {
                return $this->addArgument($dom, $name, 'null', $value, $xpath, $tag);
            }
            if (is_array($arguments)) {
                $xpath = $this->addArgument($dom, $name, 'array', null, $xpath, $tag);
                return $this->addArguments($dom, $value, $xpath, 'item');
            }
        }
    }

    private function addArgument(Dom &$dom, $name, $type, $value, $xpath, $tag)
    {
        $dom->updateElement($tag, 'name', $name, $value, $xpath);
        array_push($xpath, $tag.'[@name="'.$name.'"]');
        $dom->updateAttribute('xsi:type', $type, $xpath);
        return $xpath;
    }
}