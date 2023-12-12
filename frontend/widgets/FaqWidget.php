<?php
namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\bootstrap\Collapse;
use common\widgets\DbText;


class FaqWidget extends Widget
{
    public $header;
    public $label;
    public $content;
    private $items;

    public function init()
    {
        parent::init();

        /*
        $label = [
        
            "How do I come to Nepal?",
            "Which is best time of the year to travel?",
       
        ];*/
        /*
        $content = [
            "You have to book your own international flight from your country to Nepal and back according to your trip departure dates.",
            "Most of the trekking routes are open throughout the year but September through to December and March to May are the best months to travel.",
        ];
        */


        $this->items = array();
        for ($i = 0 ; $i<count($this->label); $i++) {
            $item = [
                'label'=>'<i class="fa fa-check-circle" aria-hidden="true"></i> &nbsp;' . $this->label[$i], 
                'content'=>$this->content[$i]
                ];
            $this->items[] = $item;
        }

    }

    public function run()
    {
        $html = "";

        if ($this->header != "") {
            echo "<h3 class='menu-title menu-title-tight'>" . Html::encode($this->header) . "</h3>";
            echo "<h5>Frequently Asked Questions</h5>";
        }
        $html = Collapse::widget([
            'items' => $this->items,
            'encodeLabels' => false,
            'options' => ["class"=>"faq-collapse"],
        ]);
        
        $html .= "<br><br>";

        return $html;
    }
}