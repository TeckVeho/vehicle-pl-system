<?php

namespace App\Imports;

use App\Models\LineworkBotMessage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithStartRow;

class LineworkBotMessageImport implements ToModel, WithStartRow, WithLimit
{
    /**
     * Bỏ qua dòng 1 (header). Chỉ đọc từ dòng 2.
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Chỉ đọc 399 dòng (dòng 2–400). Dừng hẳn ở dòng 400, không đọc thêm.
     */
    public function limit(): int
    {
        return 398;
    }

    public function model(array $row)
    {
        $this->mapAndSaveData($row);
    }

    /**
     * Trích xuất text từ ô chứa công thức =IFERROR(..., "fallback").
     * Google Sheets dùng GOOGLETRANSLATE trong IFERROR; PhpSpreadsheet không tính được
     * nên trả về chuỗi công thức. Fallback chứa bản dịch thực tế.
     */
    private function extractFormulaFallback($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }
        if (!is_string($value)) {
            return $value;
        }
        $v = trim($value);
        if (!str_starts_with($v, '=IFERROR(')) {
            return $value;
        }
        $anchor = '"),"';
        $pos = strrpos($v, $anchor);
        if ($pos === false) {
            return $value;
        }
        $start = $pos + strlen($anchor);
        $len = strlen($v);
        $result = '';
        $i = $start;
        while ($i < $len) {
            if ($v[$i] === '"') {
                if ($i + 1 < $len && $v[$i + 1] === '"') {
                    $result .= '"';
                    $i += 2;
                    continue;
                }
                if ($i + 1 < $len && $v[$i + 1] === ')') {
                    return $result;
                }
            }
            $result .= $v[$i];
            $i++;
        }
        return $value;
    }

    private function mapAndSaveData($row)
    {
        $date = $row[0] ?? null;
        $message = $row[1] ?? null;
        $messageEn = $this->extractFormulaFallback($row[2] ?? null);
        $messageZh = $this->extractFormulaFallback($row[3] ?? null);
        if (empty($date)) {
            return;
        }

        if (is_numeric($date)) {
            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
            $month = $excelDate->format('n');
            $day = $excelDate->format('j');
        } else {
            $dateParts = explode('/', $date);
            $month = isset($dateParts[0]) ? trim($dateParts[0]) : null;
            $day = isset($dateParts[1]) ? trim($dateParts[1]) : null;
        }
   
        if ($month && $day) {
            $LineworkBotMessage = LineworkBotMessage::where('month', $month)->where('day', $day)->first();
            if ($LineworkBotMessage) {
                $LineworkBotMessage->update([
                    'message' => $message,
                    'message_en' => $messageEn,
                    'message_zh' => $messageZh
                ]);
            } else {
                LineworkBotMessage::create([
                    'message' => $message,
                    'day' => $day,
                    'month' => $month,
                    'status' => 0,
                    'message_en' => $messageEn,
                    'message_zh' => $messageZh
                ]);
            }
        }
    }
    
}
