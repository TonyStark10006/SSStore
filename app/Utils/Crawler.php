<?php
namespace App\Utils;

class Crawler
{
    protected $data;

    public function __construct()
    {
        //
    }

    public function download($url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            //CURLOPT_ENCODING => "gzip",
            CURLOPT_RETURNTRANSFER  => true
        ]);

        $html = curl_exec($ch);
        $html = @mb_convert_encoding($html, 'UTF-8');
        curl_close($ch);
        return $html;
    }

    public function select($html, $selector)
    {
        $model = new \DOMDocument();
        //加入xml声明标签，保证二次筛选不会出现乱码
        @$model->loadHTML('<?xml encoding="UTF-8">' . $html);
        $task = new \DOMXPath($model);
        $elements = $task->query($selector);
        $data = array();

        foreach ($elements as $element) {
            //根据标签类型保存信息，
            $nodeType = $element->nodeType;
            if ($nodeType == 1 && in_array($nodeType, array('img'))) {
                $content = $element->getAttribute('src');
            } elseif ($nodeType == 2 || $nodeType == 3 || $nodeType == 4) {
                $content = $element->nodeValue;
            } else {
                //去除选取标签本身
                $content = preg_replace(
                    array("#^<{$element->nodeName}.*>#isU","#</{$element->nodeName}>$#isU"),
                    array('', ''),
                    $model->saveXML($element)
                );
            }
            $data[] = $content;
        }

        if (empty($data)) {
            return false;
        }

        app('debugbar')->info($data);
        return count($data) > 1 ? $data : $data[0];
    }

    public function extraRule($html, $pattern, $replacement)
    {
        return preg_replace($pattern, $replacement, $html);
    }
}