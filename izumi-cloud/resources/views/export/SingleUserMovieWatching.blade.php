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
            @if(Arr::get($value->user,'name'))
                <tr>
                    <td class="s3">{{Arr::get($value,'date')}} {{Arr::get($value,'time') ? Arr::get($value,'time') : '00:00'}}</td>
                    <td class="s3">{{Arr::get($value->movie,'title')}}</td>
                    <td class="s3">{{Arr::get($value->user,'department') ? Arr::get($value->user->department,'name') : ''}}</td>
                    <td class="s3">{{Arr::get($value->user,'name')}}</td>
                </tr>
            @endif

        @endforeach
        </tbody>
    </table>
</div>
