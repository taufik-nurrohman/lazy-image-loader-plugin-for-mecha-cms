<?php

if( ! isset($_GET['js']) || (int) $_GET['js'] > 0) {

    // Redirect to `?js=0` if JavaScript is disabled
    if( ! Config::get('__js_meta_1', false)) {
        Weapon::add('meta', function() use($config) {
            echo O_BEGIN . '<noscript><meta http-equiv="refresh" content="0;URL=' . $config->url_current . str_replace('&', '&amp;', HTTP::query('js', 0)) . '"></noscript>' . O_END;
        });
        Config::set('__js_meta_1', true);
    }

    // Include the lazy image loader plugin
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript(__DIR__ . DS . 'assets' . DS . 'sword' . DS . 'lazy-image-loader.js');
    });

    // Lazy loading image(s) in widget
    Filter::add('widget', function($content) {
        if(strpos($content, '<img ') === false) return $content;
        return preg_replace_callback('#<img(.*?)>#', function($matches) {
            if(strpos($matches[1], ' class="') === false) {
                $matches[1] = ' class="lazy"' . $matches[1];
            } else {
                $matches[1] = str_replace(' class="', ' class="lazy ', $matches[1]);
            }
            return '<img' . $matches[1] . '>';
        }, $content);
    });

    // Lazy loading comment avatar
    Filter::add('chunk:output', function($content, $path) {
        if(File::N($path) === 'comment.avatar.photo') {
            if(strpos($content, ' class="') !== false) {
                $content = str_replace(' class="', ' class="lazy ', $content);
            } else {
                $content = str_replace('<img ', '<img class="lazy" ', $content);
            }
        }
        return $content;
    });

    function do_lazy_image_loader($content) {
        if(strpos($content, '<img ') === false) return $content;
        return preg_replace_callback('#<img\s(.*?)(\s*\/?)>#', function($matches) {
            if(Text::check(array(' class="lazy"', ' class="lazy ', ' lazy ', ' lazy"'))->in(' ' . $matches[1])) {
                return '<img ' . preg_replace('#(^|\s)src=([\'"]?)#', '$1src=$2data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7$2 data-src=$2', $matches[1]) . $matches[2] . '>';
            }
            return '<img ' . $matches[1] . $matches[2] . '>';
        }, $content);
    }

    // Replace `src` attribute with `data-src` on image(s)
    Filter::add(array('widget:recent.comment', 'shield:output'), 'do_lazy_image_loader');

}