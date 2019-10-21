<?php

/*
  Plugin Name:    OTFM Extract Used Icons On The Site
  Plugin URI:     https://otshelnik-fm.ru/?p=5934
  Description:    Extract FontAwesome used icons on the site
  Version:        0.1
  Author:         Otshelnik-Fm (Wladimir Druzhaev)
  Author URI:     https://otshelnik-fm.ru/
  Text Domain:    otfm-extract-used-icons-on-the-site
  License:        GPLv3 or later
  License URI:    https://www.gnu.org/licenses/gpl-3.0.html
 */

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩

 */

// Run in admin area: site.com/?euifa=process

if ( ! defined( 'ABSPATH' ) )
    exit;

// Константа EUIFA_PATH.
if ( ! defined( 'EUIFA_URL' ) ) {
    define( 'EUIFA_URL', plugin_dir_url( __FILE__ ) );
}


/**/
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// реколл парсер fa-
//
// 1. Создадим файл из .php .js .css файлов
// 2. Получим из этого файла имена без "fa-" префикса
// 3. Получим из него юникод значения "\f***"
// 4. Массив всех 675 глифов: unicode->name
// 5. Сравнив с массивом из всех 675 глифов и переведём их в имя без "fa-" префикса
// 6. Объединим массив - это все на нашем проекте "fa-"
// 7. Прогоним по json и тех которых нет выставим "order": 0,
// 8. Сохраним json - можно импортировать его в icoMoon



function euifa_process() {
    // Подключим класс.
    if ( ! class_exists( 'IcoToJsonGenerator' ) ) {
        include_once dirname( __FILE__ ) . '/classes/class-ico-to-json-generator.php';
    }

    return IcoToJsonGenerator::instance();
}

add_action( 'admin_init', 'euifa_init', 1 );
function euifa_init() {
    if ( ! current_user_can( 'manage_options' ) )
        return;

    if ( isset( $_GET['euifa'] ) && $_GET['euifa'] === 'process' && is_plugin_active( 'wp-recall/wp-recall.php' ) ) {
        return euifa_process();
    }
}

add_action( 'admin_notices', 'euifa_admin_notice' );
function euifa_admin_notice() {
    if ( isset( $_GET['euifa'] ) && $_GET['euifa'] === 'process' && is_plugin_active( 'wp-recall/wp-recall.php' ) ) {
        $file = WP_CONTENT_DIR . '/uploads/fa-parser/my_new_fonts.json';
        if ( ! file_exists( $file ) )
            return;

        $link = '<a download href="' . WP_CONTENT_URL . '/uploads/fa-parser/my_new_fonts.json">my_new_fonts.json</a>';

        echo '<div class="notice notice-success is-dismissible">
             <p>Ваша ссылка на файл ' . $link . ' для импорта в icomoon.io</p>
         </div>';
    }
}
