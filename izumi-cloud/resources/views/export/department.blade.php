<?php
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
</style>
<div class="ritz grid-container" dir="ltr">
    <table class="waffle" cellspacing="0" cellpadding="0">
        <thead>
        <tr style="height: 20px">
            <th class="s0">ID</th>
            <th class="s0">拠点名</th>
            <th class="s0">都道府県</th>
            <th class="s0">郵便番号</th>
            <th class="s0">住所</th>
            <th class="s0">電話番号</th>
            <th class="s0">基本情報・FAX番号</th>
            <th class="s0">面接住所</th>
            <th class="s0">面接住所URL</th>
            <th class="s0">面接住所までの経路</th>
            <th class="s0">採用担当者</th>
            <th class="s0">Line Works アカウント名</th>
            <th class="s0">営業所・名称</th>
            <th class="s0">営業所・位置</th>
            <th class="s0">事務室面積(㎡)</th>
            <th class="s0">休憩室面積(㎡)</th>
            <th class="s0">車庫①・位置</th>
            <th class="s0">車庫①・面積(㎡)</th>
            <th class="s0">車庫②・位置</th>
            <th class="s0">車庫②・面積(㎡)</th>
            <th class="s0">統括運行管理者・選任</th>
            <th class="s0">運行管理者・選任</th>
            <th class="s0">運行管理者・補助者</th>
            <th class="s0">整備管理者・選任</th>
            <th class="s0">整備管理者・補助者</th>
            <th class="s0">トラック協会・会員No.</th>
            <th class="s0">Ｇマーク番号</th>
            <th class="s0">Gマーク有効期限</th>
            <th class="s0">実施/受け</th>
            <th class="s0">遠隔点呼</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $department)
            @if($department)
            <tr style="height: 20px">
                <td class="s3">{{ $department->id ?? '' }}</td>
                <td class="s2">{{ $department->name ?? '' }}</td>
                <td class="s3">{{ $department->province_name ?? '' }}</td>
                <td class="s2">{{ $department->post_code ?? '' }}</td>
                <td class="s2">{{ $department->address ?? '' }}</td>
                <td class="s2" style="mso-number-format:'\@';">{{ $department->tel ? "'" . $department->tel : '' }}</td>
                <td class="s3" style="mso-number-format:'\@';">{{ $department->maintenance_manager_fax_number ? "'" . $department->maintenance_manager_fax_number : '' }}</td>
                <td class="s2">{{ $department->interview_address ?? '' }}</td>
                <td class="s2">{{ $department->interview_address_url ?? '' }}</td>
                <td class="s2">{{ $department->path_for_interview_address ?? '' }}</td>
                <td class="s3">{{ $department->interview_pic ?? '' }}</td>
                <td class="s3">{{ $department->interview_pic_line_work ?? '' }}</td>
                <td class="s2">{{ $department->office_name ?? '' }}</td>
                <td class="s2">{{ $department->office_location ?? '' }}</td>
                <td class="s2">{{ $department->office_area ?? '' }}</td>
                <td class="s2">{{ $department->rest_room_area ?? '' }}</td>
                <td class="s2">{{ $department->garage_location_1 ?? '' }}</td>
                <td class="s2">{{ $department->garage_area_1 ?? '' }}</td>
                <td class="s2">{{ $department->garage_location_2 ?? '' }}</td>
                <td class="s2">{{ $department->garage_area_2 ?? '' }}</td>
                <td class="s2">{{ $department->chief_operations_manager_employees }}</td>
                <td class="s2">
                    @if(isset($department->operations_manager_appointment_employees) && $department->operations_manager_appointment_employees && $department->operations_manager_appointment_employees->isNotEmpty())
                        @php
                            $names = [];
                            foreach($department->operations_manager_appointment_employees as $employee) {
                                if(isset($employee->name)) {
                                    $names[] = $employee->name;
                                }
                            }
                            echo implode(', ', $names);
                        @endphp
                    @else
                        {{ '' }}
                    @endif
                </td>
                <td class="s2">
                    @if(isset($department->operations_manager_assistant_employee) && $department->operations_manager_assistant_employee && $department->operations_manager_assistant_employee->isNotEmpty())
                        @php
                            $names = [];
                            foreach($department->operations_manager_assistant_employee as $assistant) {
                                if(isset($assistant->name)) {
                                    $names[] = $assistant->name;
                                }
                            }
                            echo implode(', ', $names);
                        @endphp
                    @else
                        {{ '' }}
                    @endif
                </td>
                <td class="s2">
                    @if(isset($department->maintenance_manager_appointment_employees) && $department->maintenance_manager_appointment_employees && $department->maintenance_manager_appointment_employees->isNotEmpty())
                        @php
                            $names = [];
                            foreach($department->maintenance_manager_appointment_employees as $employee) {
                                if(isset($employee->name)) {
                                    $names[] = $employee->name;
                                }
                            }
                            echo implode(', ', $names);
                        @endphp
                    @else
                        {{ '' }}
                    @endif
                </td>
                <td class="s2">
                    @if(isset($department->maintenance_manager_assistant_employees) && $department->maintenance_manager_assistant_employees && $department->maintenance_manager_assistant_employees->isNotEmpty())
                        @php
                            $names = [];
                            foreach($department->maintenance_manager_assistant_employees as $assistant) {
                                if(isset($assistant->name)) {
                                    $names[] = $assistant->name;
                                }
                            }
                            echo implode(', ', $names);
                        @endphp
                    @else
                        {{ '' }}
                    @endif
                </td>
                <td class="s2">{{ $department->truck_association_membership_number ?? '' }}</td>
                <td class="s2">{{ $department->g_mark_number ?? '' }}</td>
                <td class="s2">{{ $department->g_mark_expiration_date ?? '' }}</td>
                <td class="s3">
                    @php
                        $gMarkActions = $department->g_mark_action_radio ?? null;
                        if (is_string($gMarkActions)) {
                            $gMarkActions = json_decode($gMarkActions, true) ?: [];
                        }
                        $gMarkActions = is_array($gMarkActions) ? $gMarkActions : (is_object($gMarkActions) ? (method_exists($gMarkActions, 'toArray') ? $gMarkActions->toArray() : []) : []);
                    @endphp
                    @if(!empty($gMarkActions))
                        @php
                            $labels = [];
                            foreach ($gMarkActions as $action) {
                                if ($action == 1) {
                                    $labels[] = '実施';
                                } elseif ($action == 2) {
                                    $labels[] = '受け';
                                }
                            }
                            echo implode(', ', $labels);
                        @endphp
                    @else
                        {{ '' }}
                    @endif
                </td>
                <td class="s3">
                    @if($department->it_roll_call == 1)
                        可
                    @elseif($department->it_roll_call == 2)
                        否
                    @endif
                </td>
            </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>
