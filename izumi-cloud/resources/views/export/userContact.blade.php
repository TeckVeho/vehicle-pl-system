<div class="ritz grid-container" dir="ltr">
    <table class="waffle" cellspacing="0" cellpadding="0">
        <thead>
        <tr>
            <th colspan="3" style="text-align: center;font-weight: bold; font-size: 11px;">所属情報</th>
            <th colspan="4" style="text-align: center;font-weight: bold; font-size: 11px;">個人連絡先情報</th>
            <th colspan="3" style="text-align: center;font-weight: bold; font-size: 11px;">緊急連絡先　情報①</th>
            <th colspan="3" style="text-align: center;font-weight: bold; font-size: 11px;">緊急連絡先　情報②</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td style="text-align: center; padding: 12px; min-width: 120px;">社員番号</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">氏名</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">勤務地</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">郵便番号</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">住所</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">電話番号</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">個人用携帯電話１</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">氏名</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">続柄</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">電話番号</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">氏名</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">続柄</td>
            <td style="text-align: center; padding: 12px; min-width: 120px;">電話番号</td>
        </tr>
        @foreach($data as $value)
            <tr>
                <td class="s3">{{$value->user_id}}</td>
                <td class="s3">{{$value->user->name}}</td>
                <td class="s3">{{$value->user->department->name}}</td>
                <td class="s3">{{$value->post_code}}</td>
                <td class="s3">{{$value->address}}</td>
                <td class="s3">{{$value->tel}}</td>
                <td class="s3">{{$value->personal_tel}}</td>
                @if (!empty($value->userContactInfos) && $value->userContactInfos)
                    @if(count($value->userContactInfos) > 1)
                        @foreach($value->userContactInfos as $user_contact_infos)
                            <td class="s3">{{$user_contact_infos->urgent_contact_name}}</td>
                            <td class="s3">{{$user_contact_infos->urgent_contact_relation}}</td>
                            <td class="s3">{{$user_contact_infos->urgent_contact_tel}}</td>
                        @endforeach
                    @else

                        @if(count($value->userContactInfos) == 1 && $value->userContactInfos[0]->group == 1)
                            <td class="s3">{{$value->userContactInfos[0]->urgent_contact_name}}</td>
                            <td class="s3">{{$value->userContactInfos[0]->urgent_contact_relation}}</td>
                            <td class="s3">{{$value->userContactInfos[0]->urgent_contact_tel}}</td>
                        @elseif(count($value->userContactInfos) == 1 && $value->userContactInfos[0]->group == 2)
                            <td class="s3"></td>
                            <td class="s3"></td>
                            <td class="s3"></td>
                            <td class="s3">{{$value->userContactInfos[0]->urgent_contact_name}}</td>
                            <td class="s3">{{$value->userContactInfos[0]->urgent_contact_relation}}</td>
                            <td class="s3">{{$value->userContactInfos[0]->urgent_contact_tel}}</td>
                        @endif
                    @endif

                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
