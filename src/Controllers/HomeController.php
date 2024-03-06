<?php

namespace Slowlyo\OwlAdmin\Controllers;

use Slowlyo\OwlAdmin\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeController extends AdminController
{
    public function index(): JsonResponse|JsonResource
    {
        $page = $this->basePage()->css($this->css())->body([
            amis()->Grid()->columns([
                $this->frameworkInfo()->md(5),
                amis()->Flex()->items([
                    $this->pieChart(),
                    $this->cube(),
                ]),
            ]),
            amis()->Grid()->columns([
                $this->lineChart()->md(8),
                amis()->Flex()->className('h-full')->items([
                    $this->clock(),
                    $this->hitokoto(),
                ])->direction('column'),
            ]),
        ]);

        return $this->response()->success($page);
    }

    /**
     * 一言
     */
    public function hitokoto()
    {
        return Card::make()
            ->className('h-full clear-card-mb')
            ->body(
                Custom::make()->html(<<<HTML
<div class="h-full flex flex-col mt-5 py-5 px-7">
    <div>『</div>
    <div class="flex flex-1 items-center w-full justify-center" id="hitokoto">
        <a class="text-dark" href="#" id="hitokoto_text" target="_blank"></a>
    </div>
    <div class="flex justify-end">』</div>
</div>
<div class="flex justify-end mt-3">
    ——&nbsp;
    <span id="hitokoto_from_who"></span>
    <span>「</span>
    <span id="hitokoto_from"></span>
    <span>」</span>
</div>
HTML

                )->onMount(<<<JS
fetch('https://v1.hitokoto.cn?c=i')
    .then(response => response.json())
    .then(data => {
      const hitokoto = document.querySelector('#hitokoto_text')
      hitokoto.href = `https://hitokoto.cn/?uuid=\${data.uuid}`
      hitokoto.innerText = data.hitokoto
      document.querySelector('#hitokoto_from_who').innerText = data.from_who
      document.querySelector('#hitokoto_from').innerText = data.from
    })
    .catch(console.error)
JS
                )
            );
    }

    public function clock()
    {
        return amis()->Card()->className('h-full bg-blingbling')->header([
            'title' => '时钟',
        ])->body([
            amis()->Custom()
                ->name('clock')
                ->html('<div id="clock" class="text-4xl"></div><div id="clock-date" class="mt-5"></div>')
                ->onMount(<<<JS
const clock = document.getElementById('clock');
const tick = () => {
    clock.innerHTML = (new Date()).toLocaleTimeString();
    requestAnimationFrame(tick);
};
tick();

const clockDate = document.getElementById('clock-date');
clockDate.innerHTML = (new Date()).toLocaleDateString();
JS

                ),
        ]);
    }

    public function frameworkInfo()
    {
        return amis()->Card()->className('h-96')->body(
            amis()->Wrapper()->className('h-full')->body([
                amis()
                    ->Flex()
                    ->className('h-full')
                    ->direction('column')
                    ->justify('center')
                    ->alignItems('center')
                    ->items([
                        amis()->Image()->src(url(Admin::config('admin.logo'))),
                        amis()->Wrapper()->className('text-3xl mt-9')->body(Admin::config('admin.name')),
                        amis()->Flex()->className('w-64 mt-5')->justify('space-around')->items([
                            amis()->Action()
                                ->level('link')
                                ->label('GitHub')
                                ->blank(true)
                                ->actionType('url')
                                ->blank(true)
                                ->link('https://github.com/slowlyo/owl-admin'),
                            amis()->Action()
                                ->level('link')
                                ->label('OwlAdmin 文档')
                                ->blank(true)
                                ->actionType('url')
                                ->link('http://doc.owladmin.com'),
                            amis()->Action()
                                ->level('link')
                                ->label('Amis 文档')
                                ->blank(true)
                                ->actionType('url')
                                ->link('https://aisuda.bce.baidu.com/amis/zh-CN/docs/index'),
                        ]),
                    ]),
            ])
        );
    }

    public function pieChart()
    {
        return amis()->Card()->className('h-96')->body(
            amis()->Chart()->height(350)->config("{
  backgroundColor:'',
  tooltip: { trigger: 'item' },
  legend: { bottom: 0, left: 'center' },
  series: [
    {
      name: 'Access From',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
      label: { show: false, position: 'center' },
      emphasis: {
        label: { show: true, fontSize: '40', fontWeight: 'bold' }
      },
      labelLine: { show: false },
      data: [
        { value: 1048, name: 'Search Engine' },
        { value: 735, name: 'Direct' },
        { value: 580, name: 'Email' },
        { value: 484, name: 'Union Ads' },
        { value: 300, name: 'Video Ads' }
      ]
    }
  ]
}")
        );
    }

    public function lineChart()
    {
        $randArr = function () {
            $_arr = [];
            for ($i = 0; $i < 7; $i++) {
                $_arr[] = random_int(10, 200);
            }
            return '[' . implode(',', $_arr) . ']';
        };

        $random1 = $randArr();
        $random2 = $randArr();

        $chart = amis()->Chart()->height(380)->className('h-96')->config("{
backgroundColor:'',
title:{ text: '会员增长情况', },
tooltip: { trigger: 'axis' },
xAxis: { type: 'category', boundaryGap: false, data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] },
yAxis: { type: 'value' },
grid:{ left: '7%', right:'3%', top: 60, bottom: 30, },
legend: { data: ['访问量','注册量'] },
series: [
    { name: '访问量', data: {$random1}, type: 'line', areaStyle: {}, smooth: true, symbol: 'none', },
    { name:'注册量', data: {$random2}, type: 'line', areaStyle: {}, smooth: true, symbol: 'none', },
]}");

        return amis()->Card()->className('clear-card-mb')->body($chart);
    }

    public function cube()
    {
        return amis()->Card()->className('h-96 ml-4 w-8/12')->body(
            amis()->Html()->html(<<<HTML
<style>
    .cube-box{ height: 300px; display: flex; align-items: center; justify-content: center; }
  .cube { width: 100px; height: 100px; position: relative; transform-style: preserve-3d; animation: rotate 10s linear infinite; }
  .cube:after {
    content: '';
    width: 100%;
    height: 100%;
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.2);
    position: absolute;
    transform-origin: bottom;
    transform-style: preserve-3d;
    transform: rotateX(90deg) translateY(50px) translateZ(-50px);
    background-color: rgba(0, 0, 0, 0.1);
  }
  .cube div {
    background-color: rgba(64, 158, 255, 0.7);
    position: absolute;
    width: 100%;
    height: 100%;
    border: 1px solid rgb(27, 99, 170);
    box-shadow: 0 0 60px rgba(64, 158, 255, 0.7);
  }
  .cube div:nth-child(1) { transform: translateZ(-50px); animation: shade 10s -5s linear infinite; }
  .cube div:nth-child(2) { transform: translateZ(50px) rotateY(180deg); animation: shade 10s linear infinite; }
  .cube div:nth-child(3) { transform-origin: right; transform: translateZ(50px) rotateY(270deg); animation: shade 10s -2.5s linear infinite; }
  .cube div:nth-child(4) { transform-origin: left; transform: translateZ(50px) rotateY(90deg); animation: shade 10s -7.5s linear infinite; }
  .cube div:nth-child(5) { transform-origin: bottom; transform: translateZ(50px) rotateX(90deg); background-color: rgba(0, 0, 0, 0.7); }
  .cube div:nth-child(6) { transform-origin: top; transform: translateZ(50px) rotateX(270deg); }

  @keyframes rotate {
    0% { transform: rotateX(-15deg) rotateY(0deg); }
    100% { transform: rotateX(-15deg) rotateY(360deg); }
  }
  @keyframes shade { 50% { background-color: rgba(0, 0, 0, 0.7); } }
</style>
<div class="cube-box">
    <div class="cube">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
HTML

            )
        );
    }

    private function css(): array
    {
        return [
            '.clear-card-mb'                 => [
                'margin-bottom' => '0 !important',
            ],
            '.cxd-Image'                     => [
                'border' => '0',
            ],
            '.bg-blingbling'                 => [
                'color'             => '#fff',
                'background'        => 'linear-gradient(to bottom right, #2C3E50, #FD746C, #FF8235, #ffff1c, #92FE9D, #00C9FF, #a044ff, #e73827)',
                'background-repeat' => 'no-repeat',
                'background-size'   => '1000% 1000%',
                'animation'         => 'gradient 60s ease infinite',
            ],
            '@keyframes gradient'            => [
                '0%{background-position:0% 0%}
                  50%{background-position:100% 100%}
                  100%{background-position:0% 0%}',
            ],
            '.bg-blingbling .cxd-Card-title' => [
                'color' => '#fff',
            ],
        ];
    }
}
