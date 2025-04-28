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
            'break_start'  => ['array'],
            'break_end'    => ['array'],
            'note'         => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'note.required'       => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $validated['start_time'] ?? null;
            $endTime = $validated['end_time'] ?? null;
            $breakStarts = $this->input('break_start', []);
            $breakEnds = $this->input('break_end', []);

            // 出勤時間が退勤時間より後ならエラー
            if ($startTime && $endTime && $startTime > $endTime) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩開始・終了時間が勤務時間を超えているならエラー
            foreach ($breakStarts as $index => $breakStart) {
                if ($breakStart) {
                    if (($startTime && $breakStart < $startTime) || ($endTime && $breakStart > $endTime)) {
                        $validator->errors()->add("break_start.$index", '休憩時間が勤務時間外です');
                    }
                }
            }

            foreach ($breakEnds as $index => $breakEnd) {
                if ($breakEnd) {
                    if (($startTime && $breakEnd < $startTime) || ($endTime && $breakEnd > $endTime)) {
                        $validator->errors()->add("break_end.$index", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}