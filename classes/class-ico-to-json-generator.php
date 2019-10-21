<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IcoToJsonGenerator {

    protected static $_instance = null;
    // дефолтный файл с icomoon
    public $json_icomoon_in;
    // абсолютный путь до плагина wp-recall
    public $recall_path;
    // абсолютный путь до промежуточного файла
    public $file_plain_code_path;
    // урл до промежуточного файла
    public $file_plain_code_url;
    // куда сохраняем
    public $json_icomoon_out;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {
        // уберем лимит памяти
        ini_set( 'memory_limit', '-1' );

        $this->recall_path = WP_CONTENT_DIR . '/plugins/wp-recall';

        $this->file_plain_code_path = WP_CONTENT_DIR . '/uploads/fa-parser/fa-files-plain-code.txt';
        $this->file_plain_code_url  = WP_CONTENT_URL . '/uploads/fa-parser/fa-files-plain-code.txt';

        $this->json_icomoon_in  = file_get_contents( EUIFA_URL . '/assets/font-awesome.json' );
        $this->json_icomoon_out = WP_CONTENT_DIR . '/uploads/fa-parser/my_new_fonts.json';

        // прочитаем все файлы
        $this->fa_otfm_read_in_one_file();

        // сгенерируем для icomoon json
        $this->new_json_generate();
    }

    function fa_otfm_read_in_one_file() {
        $this->create_dir();

        // пройдемся по wp-recall
        $recall_files = $this->get_object_dirs( $this->recall_path );

        // пройдемся по addons
        $addons_files = $this->get_object_dirs( WP_CONTENT_DIR . '/wp-recall/add-on' );

        // пройдемся по теме
        $theme_files = $this->get_object_dirs( WP_CONTENT_DIR . '/themes/otfm-cowabunga' );

        $data_recall = $this->get_files( $recall_files );
        $data_addon  = $this->get_files( $addons_files );
        $data_theme  = $this->get_files( $theme_files );

        $all_files = array_merge( $data_recall, $data_addon, $data_theme );

        // мы собрали пути ни все .php, .js файлы из плагина, аддонов и темы в один массив в $directories
        // запишем в файл
        // сбросим кеш файлов
        clearstatcache();

        // если еще ничего не писали
        if ( ! file_exists( $this->file_plain_code_path ) || ! filesize( $this->file_plain_code_path ) ) {
            foreach ( $all_files as $file_path ) {
                $content = file_get_contents( $file_path );

                // откроем файлы и запишем содержимое в один
                file_put_contents( $this->file_plain_code_path, $content, FILE_APPEND );
            }
        }

        // мы получили единый файл для парсинга "fa-" и "\f***"
    }

    function create_dir() {
        // создадим папку если нет
        $dir = WP_CONTENT_DIR . '/uploads/fa-parser/';

        if ( ! is_dir( $dir ) ) {
            mkdir( $dir );
        }
    }

    // получим объект RecursiveIterator содержащий файлы в директории
    function get_object_dirs( $path ) {
        return new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ) );
    }

    function get_files( $datas ) {
        $files = [];

        // исключим для админки dashicons и сам шрифт awesome
        $exclude = [
            $this->recall_path . '/admin/assets/style.css',
            $this->recall_path . '/add-on/prime-forum/admin/style.css',
            $this->recall_path . '/add-on/commerce/admin/assets/style.css',
            $this->recall_path . '/add-on/publicpost/admin/assets/style.css',
            $this->recall_path . '/assets/rcl-awesome/rcl-awesome.css',
            $this->recall_path . '/assets/rcl-awesome/rcl-awesome.min.css',
        ];

        foreach ( $datas as $file ) {
            // папки отсекаем
            if ( $file->isDir() )
                continue;

            // кроме .php .js .css файлов нам ничего не надо
            if ( ! preg_match( '/\.php$|\.js$|\.css$/', $file->getFilename() ) )
                continue;

            // исключим для админки dashicons и сам шрифт awesome
            if ( in_array( $file, $exclude ) )
                continue;

            // путь файла в массив
            $files[] = $file->getPathname();
        }

        return $files;
    }

    /*
     *
     *
     *
     *
     *
     */
    // запишем в json для icomoon
    function new_json_generate() {
        // дефолтный файл с icomoon
        $data = json_decode( $this->json_icomoon_in );

        // промаркируем те что не используем
        $selection = $this->mark_unused_icons( $data );

        // заменим
        $data->selection = $selection;

        $new_json = json_encode( $data );

        // куда запишем
        clearstatcache();

        file_put_contents( $this->json_icomoon_out, $new_json );

        // мы получили
    }

    // Прогоним по json и тех которых нет выставим "order": 0
    function mark_unused_icons( $data ) {
        // Здесь все нами используемые имена иконок
        $my_fa_names = $this->merged_all_names();

        $selection = [];

        // выставим 1 у нужных
        foreach ( $data->selection as $dat ) {
            $dat->order = 0;

            // в строке могут быть синонимы
            $ns = explode( ', ', $dat->name );
            // если есть синонимы
            if ( count( $ns ) > 1 ) {
                foreach ( $ns as $s ) {
                    if ( in_array( $s, $my_fa_names ) ) {
                        $dat->order = 1;
                        continue;
                    }
                }
            } else {
                if ( in_array( $dat->name, $my_fa_names ) ) {
                    $dat->order = 1;
                }
            }

            $selection[] = $dat;
        }

        return $selection;
    }

    // Объединим массив - это все на нашем проекте "fa-"
    function merged_all_names() {
        $in_php = $this->get_names_in_php_js();
        $in_css = $this->convert_from_unicode_to_name();

        // получим из ключей
        $normalize = array_keys( $in_php );

        // объединим массивы, удалим дубли
        $result = array_unique( array_merge( $normalize, $in_css ) );

        // переиндексируем
        $reindex = array_values( $result );

        return $reindex;
    }

    // Получим из php-js файла имена без "fa-" префикса
    // тут можно увидеть самые популярные иконки
    function get_names_in_php_js() {
        $match = array();

        $file_сontents = file_get_contents( $this->file_plain_code_url );

        // найдем из файла по регулярке все fa-
        preg_match_all( '/fa-([a-z-%]{1,})/', $file_сontents, $match );

        return $this->sort_by_count( $match[1] );
    }

    // Сравнив с массивом из всех 675 глифов и переведём их в имя без "fa-" префикса
    function convert_from_unicode_to_name() {
        // получим список всех 675
        $name_unicodes = $this->parse_all_unicodes();
        // и юникоды тех что у нас
        $my_unicode    = $this->parse_my_unicodes();

        $new = [];

        // переведем в имена, отсеив юникоды не из rcl-awesome
        foreach ( $name_unicodes as $name => $unicode ) {
            if ( ! array_key_exists( $unicode, $my_unicode ) )
                continue;

            $new[] = $name;
        }

        return $new;
    }

    // Массив глифов: name->unicode
    function parse_all_unicodes() {
        // получим css файл rcl-awesome v16.17 и разберем его на значения
        $file_сontents = file_get_contents( EUIFA_URL . '/assets/rcl-awesome.css' );

        $match = array();

        $find = '/.rcli.fa-([a-z-0-9]*):before {[\r\n]\s*content: \"\\\\(f[a-z0-9]{1,3})\"/';

        // найдем из файла по регулярке все fa-
        preg_match_all( $find, $file_сontents, $match );

        $merged = array_combine( $match[1], $match[2] );

        return $merged;
    }

    // Получим из файла юникод значения "\f***"
    function parse_my_unicodes() {
        // регулярка на [content: "\f107";]
        $find = '/content: \"\\\\(f[a-z0-9]{1,3})\"/';

        $match = array();

        $file_сontents = file_get_contents( $this->file_plain_code_url );

        // найдем из файла по регулярке все fa-
        preg_match_all( $find, $file_сontents, $match );

        return $this->sort_by_count( $match[1] );
    }

    // сортировка по кол-ву использований
    function sort_by_count( $datas ) {
        sort( $datas );

        $srt = array_count_values( $datas );
        arsort( $srt, SORT_NATURAL );

        return $srt;
    }

    // удалим временный файл
    function __destruct() {
        if ( file_exists( $this->file_plain_code_path ) ) {
            wp_delete_file( $this->file_plain_code_path );
        }
    }

}
