<?php
    function convertGender($gender) {
        if ($gender === 0) {
            return '男性';
        } else if ($gender === 1) {
            return '女性';
        }
        return '';
    }

    function convertJobType($jobType) {
        if ($jobType === 0) {
            return 'ドライバー';
        } else if ($jobType === 1) {
            return '事務';
        } else if ($jobType === 2) {
            return 'オペレーター';
        }
        return '';
    }

    function convertEmployeeType($employeeType) {
        if ($employeeType === 0) {
            return '正社員';
        } else if ($employeeType === 1) {
            return 'パート';
        } else if ($employeeType === 3) {
            return '派遣社員';
        }
        return '';
    }

    function convertLicenseType($licenseType) {
        if ($licenseType === 0) {
            return '普通';
        } else if ($licenseType === 1) {
            return '準中型5t';
        } else if ($licenseType === 2) {
            return '準中型';
        } else if ($licenseType === 3) {
            return '中型8t';
        } else if ($licenseType === 4) {
            return '中型';
        } else if ($licenseType === 5) {
            return '大型';
        } else if ($licenseType === 6) {
            return 'けん引';
        }
        return '';
    }

    function convertEmployeeRole($employeeRole) {
        if ($employeeRole === 1) {
            return '部長';
        } else if ($employeeRole === 2) {
            return '本部長';
        } else if ($employeeRole === 3) {
            return '常務';
        } else if ($employeeRole === 4) {
            return '社長';
        }
        return '';
    }

    function convertTrainingStatus($status) {
        if ($status === 1) {
            return '完了';
        } else if ($status === 2) {
            return '未完了';
        }
        return '';
    }

    function getAptitudeTestDate($employee) {
        if (isset($employee['aptitude_assessment_forms']) && count($employee['aptitude_assessment_forms']) > 0) {
            $date = $employee['aptitude_assessment_forms'][0]['created_at'] ?? '';
            if ($date) {
                return date('Y-m-d', strtotime($date));
            }
        }
        return '';
    }

    function getHealthCheckupDate($employee) {
        if (isset($employee['health_examination_results']) && count($employee['health_examination_results']) > 0) {
            $date = $employee['health_examination_results'][0]['created_at'] ?? '';
            if ($date) {
                return date('Y-m-d', strtotime($date));
            }
        }
        return '';
    }
?>

<div class="ritz grid-container" dir="ltr">
<table class="waffle" cellspacing="0" cellpadding="0">
    <thead>
    <tr>
        <th>従業員番号 (*)</th>
        <th>氏名 (*)</th>
        <th>氏名ふりがな (*)</th>
        <th>性別 (*)</th>
        <th>生年月日 (*)</th>
        <th>選任年月日</th>
        <th>住所 (*)</th>
        <th>連絡先電話番号</th>
        <th>職種 (*)</th>
        <th>雇用区分 (*)</th>
        <th>役職</th>
        <th>免許種別 (*)</th>
        <th>入社日 (*)</th>
        <th>退職日 (*)</th>
        <th>前職履歴</th>
        <th>適性診断受診日 (*)</th>
        <th>健康診断受診日 (*)</th>
        <th>初任運転者講習 (座学)</th>
        <th>初任運転者講習 (実技)</th>
        <th>法定福利費 (*)</th>
        <th>適齢面談</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $employee)
        <tr>
            <td class="s3">{{ $employee['employee_code'] ?? '' }}</td>
            <td class="s3">{{ str_replace('/', '', $employee['name'] ?? '') }}</td>
            <td class="s3">{{ $employee['name_in_furigana'] ?? '' }}</td>
            <td class="s3">{{ convertGender($employee['sex'] ?? null) }}</td>
            <td class="s3">{{ $employee['birthday'] ?? '' }}</td>
            <td class="s3">{{ $employee['date_of_election'] ?? '' }}</td>
            <td class="s3">{{ $employee['address'] ?? '' }}</td>
            <td class="s3">{{ $employee['userContacts']['personal_tel'] ?? '' }}</td>
            <td class="s3">{{ convertJobType($employee['job_type'] ?? null) }}</td>
            <td class="s3">{{ convertEmployeeType($employee['employee_type'] ?? null) }}</td>
            <td class="s3">{{ convertEmployeeRole($employee['employee_role'] ?? null) }}</td>
            <td class="s3">{{ convertLicenseType($employee['license_type'] ?? null) }}</td>
            <td class="s3">{{ $employee['hire_start_date'] ?? '' }}</td>
            <td class="s3">{{ $employee['retirement_date'] ?? '' }}</td>
            <td class="s3">{{ $employee['previous_employment_history'] ?? '' }}</td>
            <td class="s3">{{ $employee['aptitude_assessment_forms_value'] ?? '' }}</td>
            <td class="s3">{{ $employee['health_examination_results'][0]['date_of_visit'] ?? '' }}</td>
            <td class="s3">{{ convertTrainingStatus($employee['beginner_driver_training_classroom'] ?? null) }}</td>
            <td class="s3">{{ convertTrainingStatus($employee['beginner_driver_training_practical'] ?? null) }}</td>
            <td class="s3">{{ $employee['welfare_expense'] ? number_format($employee['welfare_expense'], 2, '.', '') : '' }}</td>
            <td class="s3">{{ convertTrainingStatus($employee['age_appropriate_interview'] ?? null) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>

