<?php

use Illuminate\Support\Arr;

?>
<div class="ritz grid-container" dir="ltr">
<table class="waffle" cellspacing="0" cellpadding="0">
    <thead>
    <tr>
        <th>視聴日時</th>
        <th>タイトル</th>
        <th>所属拠点</th>
        <th>視聴者</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $key => $value)
        <tr>
            <td class="s3">{{Arr::get($value,'date')}}</td>
            <td class="s3">{{Arr::get($value->movie,'title')}}</td>
            <td class="s3">{{Arr::get($value,'user_department')}}</td>
            <td class="s3">{{Arr::get($value,'user_name')}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
