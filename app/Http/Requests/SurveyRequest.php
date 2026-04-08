<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->file('photos', []) as $index => $photo) {
                if ($photo === null || $photo->isValid()) {
                    continue;
                }

                $originalName = $photo->getClientOriginalName() ?: '名称不明';
                $size = $photo->getSize();
                $sizeLabel = $size !== false && $size !== null ? $this->formatBytes((int) $size) : '不明';
                $errorCode = $photo->getError();
                $errorDetail = $this->uploadErrorDetail($errorCode);

                $validator->errors()->add(
                    "photos.{$index}",
                    sprintf(
                        '写真%d枚目（%s / %s）のアップロードに失敗しました。原因: %s（コード: %d）。',
                        $index + 1,
                        $originalName,
                        $sizeLabel,
                        $errorDetail,
                        $errorCode
                    )
                );
            }
        });
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return sprintf('%.2f %s', $value, $units[$power]);
    }

    private function uploadErrorDetail(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'サーバーの upload_max_filesize を超えています',
            UPLOAD_ERR_FORM_SIZE => 'フォームで許可されたファイルサイズを超えています',
            UPLOAD_ERR_PARTIAL => 'ファイルが途中までしかアップロードされていません（通信不安定の可能性）',
            UPLOAD_ERR_NO_FILE => 'ファイルが送信されませんでした',
            UPLOAD_ERR_NO_TMP_DIR => '一時保存先フォルダがありません',
            UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました',
            UPLOAD_ERR_EXTENSION => 'PHP拡張モジュールによりアップロードが停止されました',
            default => '不明なアップロードエラーです',
        };
    }
}
