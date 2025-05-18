<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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

    // バリデーション前に入力値を変換
    public function prepareForValidation()
    {
        // 全角を半角に変換
        $this->merge([
            'start_time' => mb_convert_kana($this->start_time, 'a'),
            'end_time'   => mb_convert_kana($this->end_time, 'a'),
            'break_start' => array_map(function ($time) {
                return mb_convert_kana($time, 'a');
            }, $this->break_start),
            'break_end'   => array_map(function ($time) {
                return mb_convert_kana($time, 'a');
            }, $this->break_end),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $breakStarts = $this->input('break_start', []);
            $breakEnds = $this->input('break_end', []);

            // 出勤中であるか確認
            $this->validateIfNotInWork($validator);

            // 出勤・退勤時間の前後確認
            $this->validateStartEndTime($validator, $startTime, $endTime);

            // 休憩時間の勤務時間内確認と重複確認
            $this->validateBreakTimes($validator, $startTime, $endTime, $breakStarts, $breakEnds);
        });
    }

    // 出勤中であればエラー
    private function validateIfNotInWork($validator)
    {
        $userId = auth()->id();
        $isInWork = \App\Models\Attendance::where('user_id', $userId)
            ->whereNull('end_time')
            ->exists();

        if ($isInWork) {
            $validator->errors()->add('start_time', '出勤中は修正できません');
        }
    }

    // 出勤・退勤時間の前後関係確認
    private function validateStartEndTime($validator, $startTime, $endTime)
    {
        if ($startTime && $endTime && Carbon::parse($startTime)->greaterThan(Carbon::parse($endTime))) {
            $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
        }
    }

    // 休憩時間の整合性確認
    private function validateBreakTimes($validator, $startTime, $endTime, $breakStarts, $breakEnds)
    {
        foreach ($breakStarts as $index => $breakStart) {
            $breakEnd = $breakEnds[$index] ?? null;

            if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {
                $validator->errors()->add("break_start.$index", '休憩時間を記入してください');
            }

            if ($breakStart && $breakEnd) {
                $this->checkBreakTimeWithinWorkingHours($validator, $startTime, $endTime, $breakStart, $breakEnd, $index);
                $this->checkBreakTimeOrder($validator, $breakStart, $breakEnd, $index);
            }
        }

        $this->checkBreakTimeOverlap($validator, $breakStarts, $breakEnds);
    }

    // 休憩時間が勤務時間内に収まっているか確認
    private function checkBreakTimeWithinWorkingHours($validator, $startTime, $endTime, $breakStart, $breakEnd, $index)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $breakStartTime = Carbon::parse($breakStart);
        $breakEndTime = Carbon::parse($breakEnd);

        if (
            $breakStartTime->lessThan($start) || $breakStartTime->greaterThan($end) ||
            $breakEndTime->lessThan($start) || $breakEndTime->greaterThan($end)
        ) {
            $validator->errors()->add("break_start.$index", '休憩時間が勤務時間外です');
        }
    }

    // 休憩時間の前後関係確認
    private function checkBreakTimeOrder($validator, $breakStart, $breakEnd, $index)
    {
        if (Carbon::parse($breakStart)->greaterThanOrEqualTo(Carbon::parse($breakEnd))) {
            $validator->errors()->add("break_start.$index", '休憩時間が不適切です');
        }
    }

    // 休憩時間の重複確認
    private function checkBreakTimeOverlap($validator, $breakStarts, $breakEnds)
    {
        $periods = [];

        foreach ($breakStarts as $index => $start) {
            $end = $breakEnds[$index] ?? null;

            if ($start && $end) {
                $periods[] = ['start' => $start, 'end' => $end, 'index' => $index];
            }
        }

        foreach ($periods as $i => $period1) {
            foreach ($periods as $j => $period2) {
                if ($i !== $j && $this->isOverlapping($period1, $period2)) {
                    $validator->errors()->add("break_start.{$period1['index']}", '休憩時間が重複しています');
                    break 2; // 最初の重複のみエラーメッセージを表示
                }
            }
        }
    }

    // 休憩時間の重複判定
    private function isOverlapping($period1, $period2)
    {
        return Carbon::parse($period1['start'])->lessThan(Carbon::parse($period2['end'])) &&
            Carbon::parse($period1['end'])->greaterThan(Carbon::parse($period2['start']));
    }
}