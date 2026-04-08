<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyRequest;
use App\Models\Survey;
use App\Services\ExcelExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SurveyController extends Controller
{
    public function create(): Response
    {
        return response()->view('surveys.create');
    }

    public function store(SurveyRequest $request): RedirectResponse
    {
        $manager = new ImageManager(new Driver());
        $surveyDateDir = Carbon::parse($request->validated('survey_date'))->format('Y-m-d');
        $photoPaths = [];

        try {
            foreach ($request->file('photos', []) as $photo) {
                $image = $manager->read($photo->getRealPath())->scaleDown(width: 1200, height: 1200);
                $encoded = $image->toJpeg(70);

                $relativePath = sprintf('photos/%s/%s.jpg', $surveyDateDir, Str::uuid()->toString());
                Storage::disk('local')->put($relativePath, (string) $encoded);
                $photoPaths[] = $relativePath;
            }
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'photos' => '画像の処理に失敗しました。JPEG / PNG / WebP 形式で、10MB以下の写真をお試しください。',
                ]);
        }

        Survey::query()->create([
            'user_id' => $request->user()->id,
            'farmer_name' => $request->validated('farmer_name'),
            'variety_name' => $request->validated('variety_name'),
            'survey_date' => $request->validated('survey_date'),
            'temperature' => $request->validated('temperature'),
            'growth_status' => $request->validated('growth_status'),
            'latitude' => $request->validated('latitude'),
            'longitude' => $request->validated('longitude'),
            'photos' => $photoPaths,
        ]);

        return redirect()->route('surveys.create')->with('status', '調査データを保存しました。');
    }

    public function showImage(Request $request, string $date, string $filename): BinaryFileResponse
    {
        abort_unless($request->user(), 403);
        abort_if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), 404);
        abort_if(!preg_match('/^[a-f0-9-]+\.jpg$/', $filename), 404);

        $relativePath = "photos/{$date}/{$filename}";
        abort_unless(Storage::disk('local')->exists($relativePath), 404);

        return response()->file(Storage::disk('local')->path($relativePath), [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadExcel(ExcelExportService $excelExportService): BinaryFileResponse
    {
        $surveys = Survey::query()->orderBy('survey_date')->orderBy('id')->get();
        return $excelExportService->download($surveys);
    }
}
