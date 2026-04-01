<?php

use Illuminate\Support\Arr;

?>
<style type="text/css">.ritz .waffle a {
    color: inherit;
}

.ritz .waffle .s0 {
    border-bottom: 1px SOLID #000000;
    border-right: 1px SOLID #000000;
    background-color: #073763;
    text-align: left;
    color: #ffffff;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s2 {
    border-bottom: 1px SOLID #000000;
    border-right: 1px SOLID #000000;
    background-color: #ffffff;
    text-align: left;
    color: #000000;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s6 {
    border-left: none;
    border-right: none;
    border-bottom: 1px SOLID #000000;
    background-color: #ffffff;
    text-align: left;
    color: #000000;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s4 {
    border-right: none;
    border-bottom: 1px SOLID #000000;
    background-color: #ffffff;
    text-align: left;
    color: #000000;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s1 {
    border-bottom: 1px SOLID #000000;
    border-right: 1px SOLID #000000;
    background-color: #073763;
    text-align: right;
    color: #ffffff;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s3 {
    border-bottom: 1px SOLID #000000;
    border-right: 1px SOLID #000000;
    background-color: #ffffff;
    text-align: right;
    color: #000000;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s5 {
    border-left: none;
    border-bottom: 1px SOLID #000000;
    background-color: #ffffff;
    text-align: left;
    color: #000000;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}</style>
<div class="ritz grid-container" dir="ltr">
    <table class="waffle" cellspacing="0" cellpadding="0">
        <thead>
        <tr style="height: 20px">
            <th class="s0"></th>
            <th class="s0"></th>
            <th class="s0"></th>
            <th class="s0"></th>
            <th class="s0"></th>
            <th class="s0" dir="ltr" colspan="32">日額運賃表</th>
        </tr>
        <tr style="height: 20px">
            <th class="s0">拠点</th>
            <th class="s0" dir="ltr">コースID</th>
            <th class="s0" dir="ltr">運賃合計</th>
            <th class="s0" dir="ltr">高速代合計</th>
            <th class="s0" dir="ltr">配送ルート</th>
            <th class="s0" dir="ltr">運行日合計</th>
            <th class="s1" dir="ltr">1</th>
            <th class="s1" dir="ltr">2</th>
            <th class="s1" dir="ltr">3</th>
            <th class="s1" dir="ltr">4</th>
            <th class="s1" dir="ltr">5</th>
            <th class="s1" dir="ltr">6</th>
            <th class="s1" dir="ltr">7</th>
            <th class="s1" dir="ltr">8</th>
            <th class="s1" dir="ltr">9</th>
            <th class="s1" dir="ltr">10</th>
            <th class="s1" dir="ltr">11</th>
            <th class="s1" dir="ltr">12</th>
            <th class="s1" dir="ltr">13</th>
            <th class="s1" dir="ltr">14</th>
            <th class="s1" dir="ltr">15</th>
            <th class="s1" dir="ltr">16</th>
            <th class="s1" dir="ltr">17</th>
            <th class="s1" dir="ltr">18</th>
            <th class="s1" dir="ltr">19</th>
            <th class="s1" dir="ltr">20</th>
            <th class="s1" dir="ltr">21</th>
            <th class="s1" dir="ltr">22</th>
            <th class="s1" dir="ltr">23</th>
            <th class="s1" dir="ltr">24</th>
            <th class="s1" dir="ltr">25</th>
            <th class="s1" dir="ltr">26</th>
            <th class="s1" dir="ltr">27</th>
            <th class="s1" dir="ltr">28</th>
            <th class="s1" dir="ltr">29</th>
            <th class="s1" dir="ltr">30</th>
            <th class="s1" dir="ltr">31</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['schedule'] as $schedule)
            <tr style="height: 20px">
                <td style="width:100px;" class="s2">{{Arr::get($schedule, "department_name")}}</td>
                <td style="width:100px;" class="s3">{{Arr::get($schedule, "course_code")}}</td>
                <td style="width:100px;" class="s3">{{Arr::get($schedule, "fare")}}</td>
                <td style="width:276px;" class="s3">{{Arr::get($schedule, "highway_fare")}}</td>
                <td style="width:276px;" class="s3">{{Arr::get($schedule, "routes_list_name")}}</td>
                <td style="width:83px;" class="s3" dir="ltr">{{Arr::get($schedule, "operating_day")}}</td>
                @for($i = 1; $i<=31; $i++)
                    <td style="width:28px;" class="s2 softmerge" dir="ltr">
                        @if(Arr::get($schedule, "schedule.".$i))
                            {{implode(",",collect(Arr::get($schedule, "schedule.".$i, []))->pluck('route_name')->toArray())}}
                        @endif
                    </td>
                @endfor
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
