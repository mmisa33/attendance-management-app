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

            // 出勤・退勤の個別エラーメッセージ
            if (!$startTime && $endTime) {
                $validator->errors()->add('start_time', '出勤時間を記入してください');
            }

            if ($startTime && !$endTime) {
                $validator->errors()->add('end_time', '退勤時間を記入してください');
            }

            // 出退勤時間の前後関係チェック
            if ($startTime && $endTime && $startTime > $endTime) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩の時間整合性と勤務時間内チェック
            foreach ($breakStarts as $index => $breakStart) {
                $breakEnd = $breakEnds[$index] ?? null;

                // 片方だけ入力されていたらエラー
                if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {
                    $validator->errors()->add("break_start.$index", '休憩時間を記入してください');
                }

                // 両方あれば勤務時間外チェック
                if ($breakStart && $breakEnd) {
                    $breakStartOut = ($startTime && $breakStart < $startTime) || ($endTime && $breakStart > $endTime);
                    $breakEndOut = ($startTime && $breakEnd < $startTime) || ($endTime && $breakEnd > $endTime);

                    if ($breakStartOut || $breakEndOut) {
                        $validator->errors()->add("break_start.$index", '休憩時間が勤務時間外です');
                    }
                }
            }

            // 休憩時間の重複チェック
            $breakPeriods = [];
            foreach ($breakStarts as $index => $breakStart) {
                $breakEnd = $breakEnds[$index] ?? null;
                if ($breakStart && $breakEnd) {
                    $breakPeriods[] = ['start' => $breakStart, 'end' => $breakEnd];
                }
            }

            // 重複チェック
            foreach ($breakPeriods as $key1 => $period1) {
                foreach ($breakPeriods as $key2 => $period2) {
                    if ($key1 !== $key2) {
                        // 開始時間が重複しているか、終了時間が重複しているかを確認
                        if (($period1['start'] < $period2['end'] && $period1['end'] > $period2['start'])) {
                            $validator->errors()->add("break_start.$key1", '休憩開始時間が重複しています');
                            $validator->errors()->add("break_end.$key1", '休憩終了時間が重複しています');
                            break 2; // 重複が見つかれば早期に終了
                        }
                    }
                }
            }
        });
    }
}