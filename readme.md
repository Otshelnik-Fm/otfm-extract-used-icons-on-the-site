## Описание:

WordPress плагин.

Полуавтоматическая кастомизация шрифтовых иконок FontAwesome ('rcl-awesome' - [WP-Recall](https://wordpress.org/plugins/wp-recall/))  

Вам не нужно грузить все 675 иконок. Скрипт отберет только те что используются на вашем сайте, а на выходе вы получите .json файл 
для импорта на сервис [icoMoon](https://icomoon.io/app/) (увы, его апи я не нашел)  
Забираете оттуда выбранные иконки и загружаете на свой сайт.  

Например у меня получились такие метрики:  

| файл | было (кб) | стало (кб) - 1-й сайт | стало (кб) - 2-й сайт |
|------|----------|------------|------------|
| rcl-awesome.woff | 180.0 | 52.7 | 48.0 |  
| rcl-awesome.woff2 | 80.04 (675 иконок) | 24.4 (212 иконок) | 22.5 (197 иконок) |  
| rcl-awesome.min.css | 33.58 | 9.08 | 8.48 |  

Скрипт запускается только из админки, и только админом. Например так <code>https://site.com/wp-admin/?euifa=process</code>  
По завершению работы вы в admin notice получите ссылку на скачивание получившегося .json файла.  
Скачиваете файл по ссылке и плагин можно отключить.  

Подробное использование читайте [здесь](https://otshelnik-fm.ru/?p=5934&utm_source=free-plugin&utm_medium=github&utm_campaign=otfm-extract-used-icons-on-the-site&utm_content=github-com&utm_term=post-5934)  

------------------------------

## Changelog  
**2019-10-21**  
v0.1  
- [x] релиз  

------------------------------

## Author  

**Wladimir Druzhaev** (Otshelnik-Fm)  

[Сайт](https://otshelnik-fm.ru/?utm_source=free-plugin&utm_medium=github&utm_campaign=otfm-extract-used-icons-on-the-site&utm_content=github-com&utm_term=home-page)  
