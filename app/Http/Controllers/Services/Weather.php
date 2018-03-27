<?php

namespace App\Http\Controllers\Services;

use App\Traits\APIMsg;
use App\Utils\Crawler;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class Weather extends Controller
{
    //
    use APIMsg;
    public function getWeatherMsg(Request $request)
    {
        $target = $request->input('city');
        if (Redis::keys($target)) {
            $wea = unserialize(Redis::get($target));
        } else {
            $crawler = new Crawler();
            $url = 'http://www.weather.com.cn/weather/' . self::getCityCode($target) . '.shtml';
            $originHtml = $crawler->download($url);
            $filtedHtml = $crawler->extraRule($originHtml,'/<[0-9]/', '&lt;3');
            $coldDress = $crawler->select($filtedHtml, "
            //ul[contains(@class, 'clearfix')]/li[@class='li2']/p | 
            //ul[contains(@class, 'clearfix')]/li[@class='li3 hot']/a/p"
            );
            $data = $crawler->select($filtedHtml,
                "//ul[contains(@class, 't clearfix')]/li[contains(@class, 'sky skyid')]");

            for ($j = 0, $z = 0; $j < count($data); $j++, $z = $z + 2) {
                $wea[$j][] = $crawler->select($data[$j], '//h1');
                $wea[$j][] = $crawler->select($data[$j], "//p[@class='wea']");
                $wea[$j][] = $crawler->select($data[$j], "//p[@class='tem']/span") . " - " .
                    $crawler->select($data[$j], "//p[@class='tem']/i");
                $wea[$j][] = $crawler->select($data[$j], "//p[@class='win']/em//@title")[0] . " - " .
                    $crawler->select($data[$j], "//p[@class='win']/em//@title")[1];
                $wea[$j][] = preg_replace("/&lt;/", '<',
                    $crawler->select($data[$j], "//p[@class='win']//i"));
                $wea[$j][] = $coldDress[$z];
                $wea[$j][] = $coldDress[$z + 1];
            }
            Redis::set($target, serialize($wea));
            Redis::expire($target, 21600);
        }
        //app('debugbar')->info($wea);
        return response()->json(
            array_merge($this->success, ['data' => $wea]),
            200, [], 256);
    }

    public function getCityCode($city)
    {
        $crawler = new Crawler();
        $result = $crawler->select(file_get_contents(__DIR__ . '/../../../../public/weatherCityCode.xml'),
            "//county[contains('{$city}', @name)]/@weathercode");
        //app('debugbar')->info($city, $result);
        //假如搜索不到城市，则默认返回广州城市代码
        if ($result) {
            return $result;
        } else {
            return 101280101;
        }
    }
}
