<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Feed;
use App\Models\Log;
use Illuminate\Support\Facades\Http;
use DateTime;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
			//определяем метод и url запроса
			$method = 'GET';
			$url = 'http://static.feed.rbc.ru/rbc/logical/footer/news.rss';
			
			/*
			 	ПАРСИНГ НОВОСТЕЙ И ЗАПИСЬ В БД
			*/
			//получаем ответ от сервера rss
			$response = Http::send($method, $url);
			//преобразовываем xml в php array
			$xml = simplexml_load_string($response, null, LIBXML_NOCDATA);
			$json = json_encode($xml);
			$array = json_decode($json, true); 
			//выделяем массив с новостями
			$channel = $array['channel'];
			$items = $channel['item'];
			//перебираем новости
			foreach ($items as $item) {
				//форматируем дату в нужный формат
				$date = DateTime::createFromFormat('D, d M Y H:i:s O', $item['pubDate']);
				$pubDate = $date->format('Y-m-d H:i:s');
				//извлекаем изображение из массива enclosure
				$eclosuseres = (isset($item['enclosure']) ? $item['enclosure'] : []);
				$image = null;
				foreach ($eclosuseres as $eclosureArray) {
					if (count($eclosureArray) == 3) {
						//если одно изображение 
						$image = (strpos($eclosureArray['type'], 'image/') == 0 ? $eclosureArray['url'] : null);
					} else {
						//если несколько изображений
						foreach ($eclosureArray as $enclosure) {
							$image = (strpos($enclosure['type'], 'image/') == 0 ? $enclosure['url'] : null);
						}
					}
				}
				//записываем в бд если нет записи с соответствующим guid новости
				Feed::firstOrCreate(
					[
						'guid' => $item['guid'],
					],
					[
						'title' => $item['title'],
						'link' => $item['link'],
						'description' => $item['description'],
						'pub_date' => $pubDate,
						'author' => (isset($item['author']) ? $item['author'] : null),
						'image' => $image,
					]
				);
			}
				
			
			/*
			 	ЛОГИРОВАНИЕ ЗАПРОСОВ
			*/
			Log::Create(
				[
					'request_method' => $method,
					'request_url' => $url,
					'response_code' => $response->status(),
					'response_body' => $response->body(),
				]
			);
        })->everyThirtyMinutes(); //частота запросов парсера
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
