<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time'    => ['required', 'date_format:H:i'],
            'end_time'      => ['required', 'date_format:H:i'],
            'break_start'   => ['array'],
            'break_end'     => ['array'],
            'note'          => ['required'],
            'break_start.*' => ['nullable', 'date_format:H:i'],
            'break_end.*'   => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required'   => '出勤時間を記入してください',
            'start_time.date_format' => '出勤時間は「HH:MM」の形式で入力してください',
            'end_time.required'     => '退勤時間を記入してください',
            'end_time.date_format'   => '退勤時間は「HH:MM」の形式で入力してください',
            'break_start.*.date_format' => '休憩開始時間は「HH:MM」の形式で入力してください',
            'break_end.*.date_format'   => '休憩終了時間は「HH:MM」の形式で入力してください',
            'note.required'         => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $breakStarts = $this->input('break_start', []);
            $breakEnds = $this->input('break_end', []);

            // 出勤・退勤時間の整合性チェック
            $this->validateStartEndTime($validator, $startTime, $endTime);

            // 休憩時間の整合性と重複チェック
            $this->validateBreakTimes($validator, $startTime, $endTime, $breakStarts, $breakEnds);
        });
    }

    // 出勤・退勤時間のバリデーション
    private function validateStartEndTime($validator, $startTime, $endTime)
    {
        if (!$startTime && $endTime) {
            $validator->errors()->add('start_time', '出勤時間を記入してください');
        }

        if ($startTime && !$endTime) {
            $validator->errors()->add('end_time', '退勤時間を記入してください');
        }

        if ($startTime && $endTime && $startTime > $endTime) {
            $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
        }
    }

    // 休憩時間の整合性と重複チェック
    private function validateBreakTimes($validator, $startTime, $endTime, $breakStarts, $breakEnds)
    {
        foreach ($breakStarts as $index => $breakStart) {
            $breakEnd = $breakEnds[$index] ?? null;

            if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {
                $validator->errors()->add("break_start.$index", '休憩時間を記入してください');
            }

            if ($breakStart && $breakEnd) {
                $this->checkBreakTimeWithinWorkingHours($validator, $startTime, $endTime, $breakStart, $breakEnd, $index);
                $this->checkBreakTimeOrder($validator, $startTime, $endTime, $breakStart, $breakEnd, $index);
            }
        }

        $this->checkBreakTimeOverlap($validator, $breakStarts, $breakEnds);
    }

    // 休憩時間が勤務時間内かチェック
    private function checkBreakTimeWithinWorkingHours($validator, $startTime, $endTime, $breakStart, $breakEnd, $index)
    {
        $breakStartOut = ($startTime && $breakStart < $startTime) || ($endTime && $breakStart > $endTime);
        $breakEndOut = ($startTime && $breakEnd < $startTime) || ($endTime && $breakEnd > $endTime);

        if ($breakStartOut || $breakEndOut) {
            $validator->errors()->add("break_start.$index", '休憩時間が勤務時間外です');
        }
    }

    // 休憩時間が出勤時間と退勤時間内かをチェック
    private function checkBreakTimeOrder($validator, $startTime, $endTime, $breakStart, $breakEnd, $index)
    {
        // すでにエラーがあるなら何もしない
        if ($validator->errors()->has("break_start.$index")) {
            return;
        }

        // 休憩の順序がおかしい or 勤務時間外に設定されている場合
        if (
            $breakStart >= $breakEnd ||
            ($startTime && $breakStart < $startTime) ||
            ($endTime && $breakEnd > $endTime)
        ) {

            $validator->errors()->add("break_start.$index", '休憩時間が不適切です');
        }
    }

    // 休憩時間の重複チェック
    private function checkBreakTimeOverlap($validator, $breakStarts, $breakEnds)
    {
        $breakPeriods = [];

        foreach ($breakStarts as $index => $breakStart) {
            $breakEnd = $breakEnds[$index] ?? null;
            if ($breakStart && $breakEnd) {
                $breakPeriods[] = ['start' => $breakStart, 'end' => $breakEnd, 'index' => $index];
            }
        }

        foreach ($breakPeriods as $key1 => $period1) {
            foreach ($breakPeriods as $key2 => $period2) {
                if ($key1 !== $key2 && $this->isOverlapping($period1, $period2)) {
                    // エラーを break_start のみに追加し、メッセージを1回だけ表示する
                    $validator->errors()->add("break_start.{$period1['index']}", '休憩時間が重複しています');
                    break 2; // 最初の重複だけ検出
                }
            }
        }
    }

    // 休憩時間が重複しているかチェック
    private function isOverlapping($period1, $period2)
    {
        return $period1['start'] < $period2['end'] && $period1['end'] > $period2['start'];
    }

}