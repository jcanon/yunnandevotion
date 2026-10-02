<?php

namespace App\Services;

use App\Support\Words;
use DOMDocument;
use Illuminate\Validation\ValidationException;

class MarkerSvg
{
    public static function clean(string $raw, string $color): string
    {
        $fail = fn () => throw ValidationException::withMessages(['svg' => Words::get('assets.svg-error')]);
        if (strlen($raw) > 50000 || preg_match('/<!DOCTYPE|<!ENTITY/i', $raw)) {
            $fail();
        }
        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $doc->loadXML($raw, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (! $loaded || ! $doc->documentElement || $doc->documentElement->localName !== 'svg') {
            $fail();
        }
        $allowed = ['svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon'];
        $attrs = ['viewBox', 'd', 'x', 'y', 'width', 'height', 'cx', 'cy', 'r', 'rx', 'ry', 'x1', 'x2', 'y1', 'y2', 'points', 'transform', 'fill', 'stroke', 'stroke-width', 'fill-rule', 'stroke-linecap', 'stroke-linejoin', 'opacity', 'fill-opacity', 'stroke-opacity'];
        foreach ($doc->getElementsByTagName('*') as $node) {
            if (! in_array($node->localName, $allowed, true) || ! in_array($node->namespaceURI, [null, '', 'http://www.w3.org/2000/svg'], true)) {
                $fail();
            }
            foreach (iterator_to_array($node->attributes) as $attr) {
                if (! in_array($attr->name, $attrs, true) || preg_match('/url\s*\(|[<>]|javascript:|data:/i', $attr->value)) {
                    $fail();
                }
                if (in_array($attr->name, ['fill', 'stroke']) && $attr->value !== 'none') {
                    $node->setAttribute($attr->name, $color);
                }
            }
        }
        $root = $doc->documentElement;
        if (! preg_match('/^\s*-?[\d.]+[ ,]+-?[\d.]+[ ,]+[\d.]+[ ,]+[\d.]+\s*$/', $root->getAttribute('viewBox'))) {
            $fail();
        }
        $root->setAttribute('fill', $color);
        $root->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        // Serialize only the root; processing instructions outside it are discarded.
        foreach (iterator_to_array((new \DOMXPath($doc))->query('//processing-instruction()')) as $instruction) {
            $instruction->parentNode->removeChild($instruction);
        }

        return $doc->saveXML($root);
    }
}
