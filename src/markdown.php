<?php
// Conversor senzill de Markdown a HTML (manual dins del programa i PDF)
// SPDX-License-Identifier: GPL-2.0-or-later

function markdown_simple(string $md): string
{
    $html = '';
    $llista = null;
    $para = [];
    $codi = false;
    $taula = false;
    $tanca = function () use (&$html, &$llista, &$para) {
        if ($para) {
            $html .= '<p>' . implode(' ', $para) . '</p>';
            $para = [];
        }
        if ($llista) {
            $html .= "</$llista>";
            $llista = null;
        }
    };
    $inline = function (string $s): string {
        $s = h($s);
        $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
        $s = preg_replace('/(?<![\w*])\*([^*]+)\*(?!\w)/', '<em>$1</em>', $s);
        return preg_replace('/\[([^\]]+)\]\((https?:[^)\s]+|#[\w-]+)\)/', '<a href="$2">$1</a>', $s);
    };
    foreach (preg_split('/\R/', $md) as $linia) {
        if (str_starts_with($linia, '```')) {
            $tanca();
            $html .= $codi ? '</pre>' : '<pre>';
            $codi = !$codi;
            continue;
        }
        if ($codi) {
            $html .= h($linia) . "\n";
            continue;
        }
        if (str_starts_with(trim($linia), '|')) {
            $cells = array_map('trim', explode('|', trim(trim($linia), '|')));
            if (preg_match('/^[\s|:-]+$/', $linia)) {
                continue; // separador de capçalera
            }
            if (!$taula) {
                $tanca();
                $html .= '<div class="taula-wrap"><table class="taula"><thead><tr><th>' . implode('</th><th>', array_map($inline, $cells)) . '</th></tr></thead><tbody>';
                $taula = true;
            } else {
                $html .= '<tr><td>' . implode('</td><td>', array_map($inline, $cells)) . '</td></tr>';
            }
            continue;
        }
        if ($taula) {
            $html .= '</tbody></table></div>';
            $taula = false;
        }
        if (trim($linia) === '') {
            $tanca();
        } elseif (preg_match('/^(#{1,4})\s+(.*)$/', $linia, $m)) {
            $tanca();
            $n = strlen($m[1]) + 1;
            $id = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($m[2])), '-');
            $html .= "<h$n id=\"" . h($id) . "\">" . $inline($m[2]) . "</h$n>";
        } elseif (preg_match('/^\s*([-*]|\d+\.)\s+(.*)$/', $linia, $m)) {
            if ($para) {
                $html .= '<p>' . implode(' ', $para) . '</p>';
                $para = [];
            }
            $tipus = ctype_digit($m[1][0]) ? 'ol' : 'ul';
            if ($llista !== $tipus) {
                if ($llista) {
                    $html .= "</$llista>";
                }
                $html .= "<$tipus>";
                $llista = $tipus;
            }
            $html .= '<li>' . $inline($m[2]) . '</li>';
        } elseif (str_starts_with($linia, '> ')) {
            $tanca();
            $html .= '<blockquote>' . $inline(substr($linia, 2)) . '</blockquote>';
        } else {
            if ($llista) {
                $html .= "</$llista>";
                $llista = null;
            }
            $para[] = $inline(trim($linia));
        }
    }
    $tanca();
    return $html . ($taula ? '</tbody></table></div>' : '');
}

