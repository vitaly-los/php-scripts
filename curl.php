<?php
// CONFIG
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'wordpress';
$excluded_domains = array('localhost', 'easy-code.ru');
$max_connections = 10;
 
$url_list = array();
$working_urls = array();
$dead_urls = array();
$not_found_urls = array();
$active = null;
 
// соединимся с MySQL
if (!mysql_connect($db_host, $db_user, $db_pass)) {
    die('Could not connect: ' . mysql_error());
}
 
if (!mysql_select_db($db_name)) {
    die('Could not select db: ' . mysql_error());
}
 
// берем все посты со ссылками в тексте
$q = "SELECT post_content FROM wp_posts
    WHERE post_content LIKE '%href=%'
    AND post_status = 'publish'
    AND post_type = 'post'";
$r = mysql_query($q) or die(mysql_error());
 
while ($d = mysql_fetch_assoc($r)) {
    // собираем все ссылки с помощью регулярки
    if (preg_match_all("/href=\"(.*?)\"/", $d['post_content'], $matches)) {
        foreach ($matches[1] as $url) {
            // фильтруем ненужные домены
            $tmp = parse_url($url);
 
            if (isset($tmp['host']) && in_array($tmp['host'], $excluded_domains)) {
                continue;
            }
 
            // собираем вместе
            $url_list [] = $url;
        }
    }
}
 
// удаляем повторения
$url_list = array_values(array_unique($url_list));
 
if (!$url_list) {
    die('No URL to check');
}


$mh = curl_multi_init();
 
// 1. добавим ссылки
for ($i = 0; $i < $max_connections; $i++) {
    add_url_to_multi_handle($mh, $url_list);
}
 
// основной цикл
do {
    curl_multi_exec($mh, $curRunning);
 
    // 2. один из потоков завершил работу
    if ($curRunning != $running) {
        $mhinfo = curl_multi_info_read($mh);
 
        if (is_array($mhinfo) && ($ch = $mhinfo['handle'])) {
            // 3. один из запросов выполнен, можно получить информацию о нем
            $info = curl_getinfo($ch);
 
            // 4. нерабочая ссылка
            if (!$info['http_code']) {
                $dead_urls[] = $info['url'];
 
            // 5. 404?
            } else if ($info['http_code'] == 404) {
                $not_found_urls[] = $info['url'];
 
            // 6. верная ссылка
            } else {
                $working_urls[] = $info['url'];
            }
 
            // 7. удаляем отработавший дескриптор
            curl_multi_remove_handle($mh, $mhinfo['handle']);
            curl_close($mhinfo['handle']);
 
            // 8. добавим новый урл
            add_url_to_multi_handle($mh, $url_list);
            $running = $curRunning;
        }
    }
} while ($curRunning > 0);
 
curl_multi_close($mh);
 
echo "==Dead URLs==\n";
echo implode("\n", $dead_urls) . "\n\n";
 
echo "==404 URLs==\n";
echo implode("\n", $not_found_urls) . "\n\n";
 
echo "==Working URLs==\n";
echo implode("\n", $working_urls);
echo "\n\n";
 
// 9. добавляет дескриптор с заданным урлом
function add_url_to_multi_handle($mh, $url_list) {
    static $index = 0;
 
    // если еще есть ссылки
    if (isset($url_list[$index])) {
        // все как обычно
        $ch = curl_init();
 
        // устанавливаем опции
        curl_setopt($ch, CURLOPT_URL, $url_list[$index]);
        // возвращаем, а не выводим результат
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // разрешаем редиректы
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // получаем только заголовки для экономии времени
        curl_setopt($ch, CURLOPT_NOBODY, 1);
 
        // добавляем к мульти-дескриптору
        curl_multi_add_handle($mh, $ch);
 
        $index++;
    }
}
