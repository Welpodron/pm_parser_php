<?
include(__DIR__ . '/simplehtmldom/simple_html_dom.php');

error_reporting(E_ALL ^ E_WARNING); // Отключение внутренних ворнингов у simple_html_dom
ini_set('max_execution_time', '0'); // Скрипт может выполняться достаточно долго 
// els - elements
// el - element
// TODO: Добавить пагинацию + в массив 
// Корень сайта относительно которого будут строиться пути
define('PARSER_SITE_ROOT_URL', 'https://pm.ru');
// Селектор всех li элементов пагинации 
define('PARSER_CATEGORY_PAGINATION_LI_ELS_SELECTOR', '.lister-block li');
// Селектор элемента содержащий ссылку на страницу продукта (ДЛЯ КАТЕГОРИИ ТОВАРОВ)
define('PARSER_PRODUCT_EL_SELECTOR', '.good__item');
// Селектор ссылки на страницу продукта (ДЛЯ КАТЕГОРИИ ТОВАРОВ)
define('PARSER_PRODUCT_URL_EL_SELECTOR', 'a.good__link');
// Селектор элемента содержащий название продукта (ДЛЯ ДЕТАЛЬНОЙ СТРАНИЦЫ)
define('PARSER_PRODUCT_NAME_EL_SELECTOR', 'h1');
// Селектор элемента содержащий ID продукта (ДЛЯ ДЕТАЛЬНОЙ СТРАНИЦЫ)
define('PARSER_PRODUCT_ID_EL_SELECTOR', '#cart-good-id');
// Атрибут элемента содержащий ID продукта (ДЛЯ ДЕТАЛЬНОЙ СТРАНИЦЫ)
define('PARSER_PRODUCT_ID_EL_ATTRIBUTE', 'value');
// Количество отзывов на один продукт
define('PARSER_MAX_REVIEWS_PER_PRODUCT', 3);
// Селектор элемента отзыва 
define('PARSER_REVIEW_EL_SELECTOR', '.opinion');
// Селектор элемента автора отзыва
define('PARSER_REVIEW_EL_AUTHOR_EL_SELECTOR', '.opinion__author span');
// Атрибут элемента отзыва, содержащий рейтинг
define('PARSER_REVIEW_EL_RATING_ATTRIBUTE', 'data-sort-rating');
// Атрибут элемента отзыва, содержащий дату отзыва
define('PARSER_REVIEW_EL_DATE_ATTRIBUTE', 'data-sort-date');
// Селектор элемента содержащий текст отзыва (комментарий, преимещуства и недостатки)
define('PARSER_REVIEW_EL_DESC_EL_SELECTOR', '.opinion__desc-block');
// Селектор элемента содержащий заголовки 'Отзыв', 'Достоинства', 'Недостатки'
define('PARSER_REVIEW_EL_DESC_h4_SELECTOR', 'h4');
// Текст элемента h4 после которого идет текст отзыва
define('PARSER_REVIEW_EL_DESC_h4_COMMENT_TEXT', 'Отзыв');
// Текст элемента h4 после которого идет текст достоинств
define('PARSER_REVIEW_EL_DESC_h4_ADVANTAGES_TEXT', 'Достоинства');
// Текст элемента h4 после которого идет текст недостатков
define('PARSER_REVIEW_EL_DESC_h4_DISADVANTAGES_TEXT', 'Недостатки');
// Селектор элемента содержащий фотографии отзыва
define('PARSER_REVIEW_EL_IMAGES_EL_SELECTOR', '.opinion__photos');
// Селектор элемента содержащий ссылку на фотографию
define('PARSER_REVIEW_EL_IMAGES_IMG_SELECTOR', 'a.opinion__photo');
// Атрибут элемента содержащий ссылку на фотографию
define('PARSER_REVIEW_EL_IMAGES_IMG_ATTRIBUTE', 'data-image-original');
// Разделитель для ссылок на изображения
define('PARSER_REVIEW_IMAGES_DELIMITER', '|');

// Формат дополнительной конвертации даты к определенному формату
// $ADDITIONAL_DATE_CONVERT_FORMAT = 'd.m.Y';

// ВНИМАНИЕ! Урл категории подставляется БЕЗ КОРНЯ САЙТА
// URL категории (ВНИМАНИЕ! НЕ ИСПОЛЬЗОВАТЬ queryParams внутри урлов!)
// ВНИМАНИЕ! Дубликаты отзывов если товар лежит в двух категориях сразу не проверяются!
$CATEGORY_URLS = ['/category/mebel-dlya-doma/stol/'];

// 20210214194900
// 2021 02 14 19 49 00
// 2021.02.14 19:49:00

$result = [];

$paginated_links = [];

function parser_parse_urls($urls = [], $paginate = true)
{
    global $result, $paginated_links;

    foreach ($urls as $category_url) {
?>
        <? $RANDOM_SLEEP_TIME_01 = rand(3, 6) ?>
        <p>
            [DEBUG][DELAY]<b>Запущено ожидание длительностью: <?= $RANDOM_SLEEP_TIME_01 ?> секунд(ы)</b>
        </p>
        <? sleep($RANDOM_SLEEP_TIME_01) ?>
        <p>
            [DEBUG][DELAY]<b>Ожидание завершено подготовка к отправке следующего запроса</b>
        </p>
        <? unset($RANDOM_SLEEP_TIME_01) ?>
        <p>
            [DEBUG] Парсинг <a href="<?= PARSER_SITE_ROOT_URL . $category_url ?>"><?= PARSER_SITE_ROOT_URL . $category_url ?></a> начат в: <?= date("d.m.Y H:i:s") ?>
        </p>
        <?

        $html_products_category = file_get_html(PARSER_SITE_ROOT_URL . $category_url);

        $html_products_category->save('wtf.html');

        die();

        if ($html_products_category) {
            if ($paginate) {
                $pagination_el_li_last_el = $html_products_category->find(PARSER_CATEGORY_PAGINATION_LI_ELS_SELECTOR, -1);

                if ($pagination_el_li_last_el) {
                    if ($pagination_el_li_last_el->class) {
                        $pagination_el_li_last_el = $html_products_category->find(PARSER_CATEGORY_PAGINATION_LI_ELS_SELECTOR, -2);
                    }
                }
                // TODO: Rework duplicate
                if ($pagination_el_li_last_el) {
                    $pagination_el_li_last_link_el = $pagination_el_li_last_el->find('a', 0);

                    if ($pagination_el_li_last_link_el) {
                        $pagination_el_li_last_link = $pagination_el_li_last_link_el->href;

                        if ($pagination_el_li_last_link) {
                            $query_str = parse_url($pagination_el_li_last_link, PHP_URL_QUERY);

                            parse_str($query_str, $query_params);

                            if ($query_params && $query_params['page']) {
                                for ($i = 2; $i < intval($query_params['page']) + 1; $i++) {
                                    $paginated_links[] = $category_url . '?page=' . $i;
                                }
                            }

                            unset($query_str, $query_params);
                        }

                        unset($pagination_el_li_last_link);

                        $pagination_el_li_last_link_el->clear();
                    }

                    unset($pagination_el_li_last_link_el);

                    $pagination_el_li_last_el->clear();
                }

                unset($pagination_el_li_last_el);
            }

            $products_els = $html_products_category->find(PARSER_PRODUCT_EL_SELECTOR);

            foreach ($products_els as $product_el) {
                $product_link_el = $product_el->find(PARSER_PRODUCT_URL_EL_SELECTOR, 0);

                if ($product_link_el) {
                    $product_link = $product_link_el->href;
                    $product_link_el->clear();
                }

                unset($product_link_el);

                if ($product_link) {
                    $product_reviews = [];

                    $product['product_url'] = PARSER_SITE_ROOT_URL . $product_link;

        ?>
                    <? $RANDOM_SLEEP_TIME_02 = rand(3, 6) ?>
                    <p>
                        [DEBUG][DELAY]<b>Запущено ожидание длительностью: <?= $RANDOM_SLEEP_TIME_02 ?> секунд(ы)</b>
                    </p>
                    <? sleep($RANDOM_SLEEP_TIME_02) ?>
                    <p>
                        [DEBUG][DELAY]<b>Ожидание завершено подготовка к отправке следующего запроса</b>
                    </p>
                    <? unset($RANDOM_SLEEP_TIME_02) ?>
                    <p>
                        [DEBUG] Парсинг <a href="<?= $product['product_url'] ?>"><?= $product['product_url'] ?></a> начат в: <?= date("d.m.Y H:i:s") ?>
                    </p>

                    <?

                    $html_product_detail = file_get_html($product['product_url']);

                    if ($html_product_detail) {
                        $product_name_el = $html_product_detail->find(PARSER_PRODUCT_NAME_EL_SELECTOR, 0);

                        if ($product_name_el) {
                            $product['product_name'] = $product_name_el->plaintext;
                            $product_name_el->clear();
                        } else {
                            // Не было найдено имя для элемента, нет смысла продолжать 
                            continue;
                        }

                        unset($product_name_el);

                        $product_id_el = $html_product_detail->find(PARSER_PRODUCT_ID_EL_SELECTOR, 0);

                        if ($product_id_el) {
                            $product['product_id'] = $product_id_el->{PARSER_PRODUCT_ID_EL_ATTRIBUTE};
                            $product_id_el->clear();
                        } else {
                            // Не было найдено Id для элемента, нет смысла продолжать 
                            continue;
                        }

                        unset($product_id_el);

                        $reviews_els = $html_product_detail->find(PARSER_REVIEW_EL_SELECTOR);

                        $reviews_counter = 0;

                        foreach ($reviews_els as $review_el) {
                            if ($reviews_counter >= PARSER_MAX_REVIEWS_PER_PRODUCT) {
                                break;
                            }

                            // Ключи по умолчанию для облегченной конвертации в CSV
                            $review = [
                                'review_rating' => '',
                                'review_author' => '',
                                'review_date' => '',
                                'review_comment' => '',
                                'review_advantages' => '',
                                'review_disadvantages' => '',
                                'review_images' => ''
                            ];

                            $review['review_rating'] = $review_el->{PARSER_REVIEW_EL_RATING_ATTRIBUTE};

                            // Игнорирование всех отзывов, которые не являются самыми лучшими
                            if ($review['review_rating'] != '5') {
                                continue;
                            }

                            $reviews_counter++;

                            $review_author_el = $review_el->find(PARSER_REVIEW_EL_AUTHOR_EL_SELECTOR, 0);

                            if ($review_author_el) {
                                $review['review_author'] = $review_author_el->plaintext;
                                $review_author_el->clear();
                            }

                            unset($review_author_el);

                            // $review['review_date'] = $review_el->{PARSER_REVIEW_EL_DATE_ATTRIBUTE};
                            $review_date_raw = $review_el->{PARSER_REVIEW_EL_DATE_ATTRIBUTE};
                            $review_date_year = substr($review_date_raw, 0, 4);
                            $review_date_month = substr($review_date_raw, 4, 2);
                            $review_date_day = substr($review_date_raw, 6, 2);
                            $review_date_hour = substr($review_date_raw, 8, 2);
                            $review_date_minute = substr($review_date_raw, 10, 2);
                            $review_date_second = substr($review_date_raw, 12, 2);
                            // 20210214194900
                            // 2021 02 14 19 49 00
                            // 2021.02.14 19:49:00
                            $review['review_date'] = $review_date_day . '.' . $review_date_month . '.' . $review_date_year . ' ' . $review_date_hour . ':' . $review_date_minute . ':' . $review_date_second;
                            // Дополнительное поле сконвертированной даты к формату Подробнее https://www.php.net/manual/ru/function.date.php
                            // $review['review_date_converted'] = date($ADDITIONAL_DATE_CONVERT_FORMAT, intval($review['review_date']));

                            $review_desc_el = $review_el->find(PARSER_REVIEW_EL_DESC_EL_SELECTOR, 0);

                            if ($review_desc_el) {
                                $review_desc_el_h4_els = $review_desc_el->find(PARSER_REVIEW_EL_DESC_h4_SELECTOR);

                                if ($review_desc_el_h4_els) {
                                    foreach ($review_desc_el_h4_els as $review_desc_el_h4_el) {
                                        $review_desc_el_h4_el_next_h4_el = $review_desc_el_h4_el->next_sibling();

                                        if ($review_desc_el_h4_el_next_h4_el) {
                                            if ($review_desc_el_h4_el->plaintext == PARSER_REVIEW_EL_DESC_h4_COMMENT_TEXT) {
                                                $review['review_comment'] = $review_desc_el_h4_el_next_h4_el->plaintext;
                                            }
                                            if ($review_desc_el_h4_el->plaintext == PARSER_REVIEW_EL_DESC_h4_ADVANTAGES_TEXT) {
                                                $review['review_advantages'] = $review_desc_el_h4_el_next_h4_el->plaintext;
                                            }
                                            if ($review_desc_el_h4_el->plaintext == PARSER_REVIEW_EL_DESC_h4_DISADVANTAGES_TEXT) {
                                                $review['review_disadvantages'] = $review_desc_el_h4_el_next_h4_el->plaintext;
                                            }

                                            $review_desc_el_h4_el_next_h4_el->clear();
                                            unset($review_desc_el_h4_el_next_h4_el);
                                        }
                                    }

                                    unset($review_desc_el_h4_el_next_h4_el);
                                }

                                $review_desc_el->clear();
                                unset($review_desc_el_h4_els);
                            }

                            unset($review_desc_el);

                            $review_images_el = $review_el->find(PARSER_REVIEW_EL_IMAGES_EL_SELECTOR, 0);

                            if ($review_images_el) {
                                $review_images_el_img_els = $review_images_el->find(PARSER_REVIEW_EL_IMAGES_IMG_SELECTOR);

                                if ($review_images_el_img_els) {
                                    $images_paths = [];

                                    foreach ($review_images_el_img_els as $review_images_el_img_el) {
                                        $images_paths[] = PARSER_SITE_ROOT_URL . $review_images_el_img_el->{PARSER_REVIEW_EL_IMAGES_IMG_ATTRIBUTE};
                                    }

                                    $review['review_images'] = implode(PARSER_REVIEW_IMAGES_DELIMITER, $images_paths);

                                    unset($images_paths);
                                }

                                unset($review_images_el_img_els);

                                $review_images_el->clear();
                            }

                            unset($review_images_el);

                            $product_reviews[] = $review;

                            unset($review);
                        }

                        unset($reviews_els);

                        $html_product_detail->clear();
                    }

                    unset($html_product_detail);

                    ?>

                    <p>
                        [DEBUG] Парсинг <a href="<?= $product['product_url'] ?>"><?= $product['product_url'] ?></a> завершен в: <?= date("d.m.Y H:i:s") ?>
                    </p>

        <?

                    // Только если у элемента есть отзывы 
                    foreach ($product_reviews as $review) {
                        $result[] = array_merge($product, $review);
                    }

                    unset($product);
                    unset($product_reviews);
                }

                unset($product_link);
            }

            unset($products_els);
            $html_products_category->clear();
        }

        unset($html_products_category);

        ?>

        <p>
            [DEBUG] Парсинг <a href="<?= PARSER_SITE_ROOT_URL . $category_url ?>"><?= PARSER_SITE_ROOT_URL . $category_url ?></a> завершен в: <?= date("d.m.Y H:i:s") ?>
        </p>

<?

    }
}

?>

<?

parser_parse_urls($CATEGORY_URLS, true);

$paginated_links = array_unique($paginated_links);

parser_parse_urls($paginated_links, false);

$fp = null;

if ($result) {
    $fp = fopen('Результат парсинга ' . date("d.m.Y H.i.s") . '.csv', 'w');

    if ($fp) {
        $csv_header = array_keys($result[0]);

        fputcsv($fp, $csv_header);

        foreach ($result as $result_item) {
            $csv_line = array_values($result_item);
            fputcsv($fp, $csv_line);
        }
    }
}

unset($result);
unset($paginated_links);

if ($fp) {
    fclose($fp);
}
