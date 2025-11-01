<?php
/**
 * Helper functions for company analysis rendering.
 *
 * @package PSIP_Theme
 */

if (!function_exists('psip_theme_format_list_text')) {
    /**
     * Split comma-separated items (with capitalized starts) into lines for readability.
     *
     * @param string $text Raw text.
     * @return string Sanitised HTML string.
     */
    function psip_theme_format_list_text($text) {
        if ($text === null || $text === '') {
            return '';
        }

        $segments = preg_split('/,\s*(?=[A-ZÀ-ÖØ-Ý])/u', $text);
        if (!$segments || count($segments) === 1) {
            return esc_html($text);
        }

        $segments = array_map(function ($segment) {
            $segment = trim($segment);
            return $segment !== '' ? esc_html($segment) : null;
        }, $segments);

        $segments = array_filter($segments, static function ($segment) {
            return $segment !== null;
        });

        return $segments ? implode('<br>', $segments) : esc_html($text);
    }
}

if (!function_exists('psip_theme_format_markdown_bold')) {
    /**
     * Minimal markdown-to-HTML helper to highlight titles with <strong>.
     *
     * @param string $text Raw markdown-like text.
     * @return string HTML string with autop + strong tags.
     */
    function psip_theme_format_markdown_bold($text) {
        if ($text === null || $text === '') {
            return '';
        }

        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/s', '<strong>$1</strong>', $text);

        return wpautop(wp_kses_post($text));
    }
}

if (!function_exists('psip_theme_is_assoc_array')) {
    function psip_theme_is_assoc_array($array) {
        if (!is_array($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}

if (!function_exists('psip_theme_pretty_label')) {
    function psip_theme_pretty_label($key) {
        $label = str_replace(['_', '-'], ' ', (string) $key);
        $label = preg_replace('/\s+/', ' ', $label);
        $label = trim($label);

        if ($label === '') {
            return '';
        }

        return mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('psip_theme_extract_structured_widgets')) {
    function psip_theme_extract_structured_widgets($decoded) {
        if (!is_array($decoded)) {
            return [];
        }

        $widgets_source = $decoded;
        if (psip_theme_is_assoc_array($decoded) && isset($decoded['widgets']) && is_array($decoded['widgets'])) {
            $widgets_source = $decoded['widgets'];
        }

        $normalise_item = static function ($item, $fallback_label = '') {
            if (!is_array($item)) {
                if ($item === null || $item === '') {
                    return null;
                }
                $item = ['dato' => $item];
            }

            $label = '';
            $label_keys = ['label', 'nome', 'name', 'title', 'titolo'];
            foreach ($label_keys as $label_key) {
                if (isset($item[$label_key]) && trim((string) $item[$label_key]) !== '') {
                    $label = trim((string) $item[$label_key]);
                    break;
                }
            }
            if ($label === '' && $fallback_label !== '') {
                $label = psip_theme_pretty_label($fallback_label);
            }

            $value = '';
            $value_keys = ['dato', 'value', 'valore', 'metric', 'dato_principale', 'numero'];
            foreach ($value_keys as $value_key) {
                if (isset($item[$value_key]) && trim((string) $item[$value_key]) !== '') {
                    $value = trim((string) $item[$value_key]);
                    break;
                }
            }
            if ($value === '' && isset($item['dato']) && $item['dato'] !== null) {
                $value = trim((string) $item['dato']);
            }

            $source = '';
            $source_keys = ['fonte', 'source', 'provenienza'];
            foreach ($source_keys as $source_key) {
                if (isset($item[$source_key]) && trim((string) $item[$source_key]) !== '') {
                    $source = trim((string) $item[$source_key]);
                    break;
                }
            }

            if ($label === '' && $value === '') {
                return null;
            }

            if ($value === '') {
                $value = '—';
            }

            $maxLength = 260;
            if (function_exists('mb_strlen')) {
                if (mb_strlen($value) > $maxLength) {
                    $value = rtrim(mb_substr($value, 0, $maxLength - 1)) . '…';
                }
                if (mb_strlen($label) > 120) {
                    $label = rtrim(mb_substr($label, 0, 119)) . '…';
                }
                if ($source !== '' && mb_strlen($source) > $maxLength) {
                    $source = rtrim(mb_substr($source, 0, $maxLength - 1)) . '…';
                }
            }

            return [
                'label' => $label,
                'value' => $value,
                'source' => $source,
            ];
        };

        $widgets = [];

        if (!psip_theme_is_assoc_array($widgets_source)) {
            foreach ($widgets_source as $item) {
                $normalised = $normalise_item($item);
                if ($normalised !== null) {
                    $widgets[] = $normalised;
                }
            }
        } else {
            foreach ($widgets_source as $key => $item) {
                $normalised = $normalise_item($item, is_string($key) ? $key : '');
                if ($normalised !== null) {
                    $widgets[] = $normalised;
                }
            }
        }

        return $widgets;
    }
}

if (!function_exists('psip_theme_prepare_structured_insights')) {
    function psip_theme_prepare_structured_insights($raw_json) {
        $result = [
            'widgets' => [],
            'metrics' => [],
            'chips' => [],
            'collections' => [],
            'tables' => [],
        ];

        if (!is_string($raw_json) || trim($raw_json) === '') {
            return $result;
        }

        $clean_json = trim($raw_json);

        if (preg_match('/```(?:json)?\s*(.+?)```/si', $clean_json, $matches)) {
            $clean_json = trim($matches[1]);
        }

        $decoded = json_decode($clean_json, true);
        if (!is_array($decoded)) {
            return $result;
        }

        $widget_items = psip_theme_extract_structured_widgets($decoded);
        if (!empty($widget_items)) {
            $result['widgets'] = $widget_items;
            if (!psip_theme_is_assoc_array($decoded) || !isset($decoded['widgets'])) {
                return $result;
            }
            unset($decoded['widgets']);
        }

        foreach ($decoded as $key => $value) {
            $label = psip_theme_pretty_label($key);
            if ($label === '') {
                $label = (string) $key;
            }

            if (is_numeric($value)) {
                $numeric_value = (float) $value;
                $percentage = null;
                if ($numeric_value >= 0 && $numeric_value <= 1) {
                    $percentage = (int) round($numeric_value * 100);
                } elseif ($numeric_value >= 0 && $numeric_value <= 100) {
                    $percentage = (int) round(min(100, $numeric_value));
                }

                $formatted = abs($numeric_value) >= 1000
                    ? number_format($numeric_value, 0, ',', '.')
                    : number_format($numeric_value, ($numeric_value == floor($numeric_value)) ? 0 : 2, ',', '.');

                $result['metrics'][] = [
                    'label' => $label,
                    'value' => $formatted,
                    'percentage' => $percentage,
                    'raw' => $numeric_value,
                ];
                continue;
            }

            if (is_bool($value)) {
                $result['metrics'][] = [
                    'label' => $label,
                    'value' => $value ? __('Sì', 'psip') : __('No', 'psip'),
                    'percentage' => $value ? 100 : 0,
                    'raw' => $value,
                ];
                continue;
            }

            if (is_array($value)) {
                if ($value === []) {
                    continue;
                }

                if (psip_theme_is_assoc_array($value)) {
                    $items = [];
                    foreach ($value as $sub_key => $sub_value) {
                        if (is_array($sub_value)) {
                            $sub_value = wp_json_encode($sub_value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        } elseif (is_bool($sub_value)) {
                            $sub_value = $sub_value ? __('Sì', 'psip') : __('No', 'psip');
                        }

                        $sub_value = trim((string) $sub_value);
                        if ($sub_value === '') {
                            continue;
                        }

                        $items[] = [
                            'label' => psip_theme_pretty_label($sub_key),
                            'value' => $sub_value,
                        ];
                    }

                    if (!empty($items)) {
                        $result['collections'][] = [
                            'title' => $label,
                            'items' => $items,
                        ];
                    }

                    continue;
                }

                $non_empty = array_filter($value, static function ($item) {
                    return $item !== null && $item !== '';
                });

                if (empty($non_empty)) {
                    continue;
                }

                $first = reset($non_empty);
                if (is_array($first)) {
                    $headers = [];
                    foreach ($non_empty as $row) {
                        if (!is_array($row)) {
                            continue;
                        }

                        $headers = array_unique(array_merge($headers, array_keys($row)));
                    }

                    if (!empty($headers)) {
                        $table_rows = [];
                        foreach ($non_empty as $row) {
                            if (!is_array($row)) {
                                continue;
                            }

                            $table_row = [];
                            foreach ($headers as $header) {
                                $cell = $row[$header] ?? '';
                                if (is_array($cell)) {
                                    $cell = wp_json_encode($cell, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                } elseif (is_bool($cell)) {
                                    $cell = $cell ? __('Sì', 'psip') : __('No', 'psip');
                                }
                                $table_row[] = trim((string) $cell);
                            }
                            $table_rows[] = $table_row;
                        }

                        if (!empty($table_rows)) {
                            $result['tables'][] = [
                                'title' => $label,
                                'headers' => array_map('psip_theme_pretty_label', $headers),
                                'rows' => $table_rows,
                            ];
                        }
                    }

                    continue;
                }

                $chips = [];
                foreach ($non_empty as $item) {
                    if (is_array($item)) {
                        $item = wp_json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    } elseif (is_bool($item)) {
                        $item = $item ? __('Sì', 'psip') : __('No', 'psip');
                    }
                    $item = trim((string) $item);
                    if ($item !== '') {
                        $chips[] = $item;
                    }
                }

                if (!empty($chips)) {
                    $result['chips'][] = [
                        'title' => $label,
                        'items' => $chips,
                    ];
                }

                continue;
            }

            $string_value = trim((string) $value);
            if ($string_value === '') {
                continue;
            }

            $result['collections'][] = [
                'title' => $label,
                'items' => [
                    [
                        'label' => null,
                        'value' => $string_value,
                    ],
                ],
            ];
        }

        return $result;
    }
}
