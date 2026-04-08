<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'farmer_name' => ['required', 'string', 'max:255'],
            'variety_name' => ['required', 'string', 'max:255'],
            'survey_date' => ['required', 'date'],
            'growth_status' => ['required', 'string'],
            'temperature' => ['required', 'numeric', 'between:0,50'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => '写真を1枚以上選択してください。',
            'photos.max' => '写真は最大10枚まで選択できます。',
            'photos.*.required' => '写真のアップロードに失敗しました。写真を選び直して再送信してください。',
            'photos.*.uploaded' => '写真のアップロードに失敗しました。通信状況、画像サイズ、画像形式（HEIC不可）をご確認ください。',
            'photos.*.image' => '写真ファイルのみアップロードできます。',
            'photos.*.mimes' => '写真は JPEG / PNG / WebP 形式のみ対応しています（HEICは非対応）。',
            'photos.*.max' => '写真1枚あたり10MB以下にしてください。',
        ];
    }
}
