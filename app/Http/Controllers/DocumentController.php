<?php

namespace App\Http\Controllers;

use App\Exports\DocumentEventsExport;
use App\Models\Document;
use App\Models\DocumentEvent;
use App\Models\DocumentTerm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\Fpdi;
use Symfony\Component\Process\Process;
use Throwable;

class DocumentController extends Controller
{
    private const WATERMARK_FIELDS = [
        'name' => 'Name',
        'email' => 'Email',
        'user_id' => 'User ID',
        'downloaded_at' => 'Downloaded at',
        'network' => 'LudoSport International Network',
    ];

    private const EVENT_LABELS = [
        'reserved_area_accessed' => 'Reserved area accessed',
        'terms_viewed' => 'Terms viewed',
        'terms_accepted' => 'Terms accepted',
        'document_downloaded' => 'Document downloaded',
    ];

    private const RESULT_LABELS = [
        'success' => 'Success',
        'failed' => 'Failed',
    ];

    public function index(): View
    {
        if (! $this->isAdmin()) {
            Document::query()->pluck('id')->each(function (int $documentId) {
                $this->logDocumentEvent(request(), $documentId, 'reserved_area_accessed', 'success');
            });
        }

        $documents = Document::query()
            ->with('uploader')
            ->latest()
            ->get()
            ->map(function (Document $document) {
                $document->author = trim(($document->uploader->name ?? '').' '.($document->uploader->surname ?? ''));
                $document->created_at_formatted = $document->created_at->format('d/m/Y H:i');

                return $document;
            });

        return view('admin.documents.index', [
            'documents' => $documents,
            'terms' => DocumentTerm::query()->with('uploader')->latest('version')->get(),
            'latestTerms' => $this->latestTerms(),
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    public function create(): View
    {
        return view('admin.documents.create', [
            'watermarkFields' => self::WATERMARK_FIELDS,
        ]);
    }

    public function events(Request $request): View
    {
        return view('admin.documents.events', [
            'events' => $this->filteredEvents($request)->get()->map(fn (DocumentEvent $event) => [
                'created_at_formatted' => $event->created_at?->format('d/m/Y H:i'),
                'user_name' => $event->user_name,
                'user_email' => $event->user?->email,
                'document_name' => $event->document?->original_name,
                'event_type' => self::EVENT_LABELS[$event->event_type] ?? Str::headline($event->event_type),
                'operation_result' => self::RESULT_LABELS[$event->operation_result] ?? Str::headline($event->operation_result),
                'terms_version' => 'V'.$event->terms_version,
                'ip_address' => $event->ip_address,
            ]),
            'documents' => Document::query()->orderBy('original_name')->get(['id', 'original_name']),
            'users' => User::query()->orderBy('name')->orderBy('surname')->get(['id', 'name', 'surname', 'email']),
            'eventTypes' => DocumentEvent::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type')->mapWithKeys(fn ($type) => [$type => self::EVENT_LABELS[$type] ?? Str::headline($type)]),
            'results' => DocumentEvent::query()->select('operation_result')->distinct()->orderBy('operation_result')->pluck('operation_result')->mapWithKeys(fn ($result) => [$result => self::RESULT_LABELS[$result] ?? Str::headline($result)]),
            'filters' => $request->only(['user_id', 'document_id', 'date_from', 'date_to', 'event_type', 'operation_result']),
        ]);
    }

    public function exportEvents(Request $request)
    {
        return Excel::download(
            new DocumentEventsExport($this->filteredEvents($request)->get(), self::EVENT_LABELS, self::RESULT_LABELS),
            'document-events-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'watermark_fields' => ['required', 'array', 'min:1'],
            'watermark_fields.*' => ['required', 'in:'.implode(',', array_keys(self::WATERMARK_FIELDS))],
            'watermark_side' => ['required', 'in:left,right'],
        ]);

        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $originalName = $file->getClientOriginalName();
        $storedName = now()->format('YmdHis').'_'.Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $storedName = trim($storedName, '_').'_'.Str::lower(Str::random(8)).'.'.$extension;
        $directory = 'documents/'.now()->format('Y/m');
        $path = $file->storeAs($directory, $storedName, 'gcs');

        if (! $path) {
            return redirect()->route('documents.index')->with('error', 'Error uploading document.');
        }

        Document::create([
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'path' => $path,
            'disk' => 'gcs',
            'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
            'watermark_fields' => array_values($request->input('watermark_fields')),
            'watermark_side' => $request->input('watermark_side'),
        ]);

        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function storeTerms(Request $request): RedirectResponse
    {
        $request->validate([
            'terms' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $request->file('terms');
        // ponytail: admin-only upload; DB unique catches rare concurrent version race. Add retry if needed.
        $version = ((int) DocumentTerm::query()->max('version')) + 1;
        $storedName = 'v'.$version.'_'.now()->format('YmdHis').'_'.Str::lower(Str::random(8)).'.pdf';
        $path = $file->storeAs('documents/terms', $storedName, 'gcs');

        if (! $path) {
            return redirect()->route('documents.index')->with('error', 'Error uploading terms.');
        }

        DocumentTerm::create([
            'version' => $version,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'path' => $path,
            'disk' => 'gcs',
            'mime_type' => $file->getClientMimeType() ?: 'application/pdf',
            'size_bytes' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('documents.index')->with('success', 'Terms uploaded successfully.');
    }

    public function downloadTerms()
    {
        $terms = $this->latestTerms();
        abort_unless($terms, 404);

        return Storage::disk($terms->disk)->download(
            $terms->path,
            'terms-of-service-v'.$terms->version.'.pdf',
            [
                'ResponseContentType' => $terms->mime_type,
                'Content-Type' => $terms->mime_type,
            ]
        );
    }

    public function download(Document $document, Request $request): RedirectResponse
    {
        return $this->downloadWatermarked($document, $request);
    }

    public function termsViewed(Document $document, Request $request): JsonResponse
    {
        abort_if($this->isAdmin(), 403);

        $this->logDocumentEvent($request, $document->id, 'terms_viewed', 'success');

        return response()->json(['ok' => true]);
    }

    public function acceptTerms(Document $document, Request $request): RedirectResponse
    {
        abort_if($this->isAdmin(), 403);

        $request->validate([
            'terms_accepted' => ['accepted'],
        ]);

        $this->logDocumentEvent($request, $document->id, 'terms_accepted', 'success');

        return $this->downloadWatermarked($document, $request);
    }

    private function downloadWatermarked(Document $document, Request $request): RedirectResponse
    {
        $disk = Storage::disk($document->disk);

        if (! $disk->exists($document->path)) {
            $this->logDocumentEvent($request, $document->id, 'document_downloaded', 'failed');

            return redirect()->route('documents.index')->with('error', 'Document not found.');
        }

        $downloadedAt = now();
        $watermarkedPath = 'documents/'.$document->id.'/downloads/'.$request->user()->id.'/'.$downloadedAt->format('YmdHis').'_'.Str::slug(pathinfo($document->original_name, PATHINFO_FILENAME)).'.pdf';

        try {
            $watermarkedPdf = $this->watermarkPdf(
                $disk->get($document->path),
                $this->watermarkLines($document, $request, $downloadedAt),
                $document->watermark_side
            );
        } catch (Throwable) {
            $this->logDocumentEvent($request, $document->id, 'document_downloaded', 'failed');

            return redirect()->route('documents.index')->with('error', 'Error preparing document download.');
        }

        $stored = $disk->put($watermarkedPath, $watermarkedPdf);

        if (! $stored) {
            $this->logDocumentEvent($request, $document->id, 'document_downloaded', 'failed');

            return redirect()->route('documents.index')->with('error', 'Error preparing document download.');
        }

        $url = $disk->temporaryUrl(
            $watermarkedPath,
            now()->addMinutes(5),
            [
                'ResponseContentType' => $document->mime_type,
                'ResponseContentDisposition' => 'attachment; filename="'.$document->original_name.'"',
            ]
        );

        $this->logDocumentEvent($request, $document->id, 'document_downloaded', 'success');

        return redirect()->away($url);
    }

    private function logDocumentEvent(Request $request, int $documentId, string $eventType, string $result): void
    {
        $user = $request->user();

        DocumentEvent::create([
            'event_type' => $eventType,
            'user_id' => $user->id,
            'user_name' => trim($user->name.' '.$user->surname),
            'document_id' => $documentId,
            'terms_version' => $this->latestTerms()?->version ?? 1,
            'operation_result' => $result,
            'ip_address' => $request->ip(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    private function filteredEvents(Request $request)
    {
        return DocumentEvent::query()
            ->with(['user:id,name,surname,email', 'document:id,original_name'])
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('document_id'), fn ($query) => $query->where('document_id', $request->integer('document_id')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->when($request->filled('event_type'), fn ($query) => $query->where('event_type', $request->input('event_type')))
            ->when($request->filled('operation_result'), fn ($query) => $query->where('operation_result', $request->input('operation_result')))
            ->latest();
    }

    private function isAdmin(): bool
    {
        return auth()->user()?->getRole() === 'admin';
    }

    private function latestTerms(): ?DocumentTerm
    {
        return DocumentTerm::query()->latest('version')->first();
    }

    private function watermarkLines(Document $document, Request $request, $downloadedAt): array
    {
        $values = [
            'name' => trim($request->user()->name.' '.$request->user()->surname),
            'email' => $request->user()->email,
            'user_id' => 'User ID: '.$request->user()->id,
            'downloaded_at' => 'Downloaded at: '.$downloadedAt->format('d/m/Y H:i:s'),
            'network' => self::WATERMARK_FIELDS['network'],
        ];

        return array_map(fn ($field) => $values[$field] ?? null, $document->watermark_fields ?? []);
    }

    private function watermarkPdf(string $sourcePdf, array $lines, string $side): string
    {
        $source = tempnam(sys_get_temp_dir(), 'document-source-');
        file_put_contents($source, $sourcePdf);

        $watermark = Str::ascii(implode(' | ', array_filter($lines)));
        $normalized = null;

        try {
            try {
                return $this->watermarkPdfTemplate($source, $watermark, $side);
            } catch (Throwable) {
                $normalized = $this->normalizePdfForFpdi($source);

                return $this->watermarkPdfTemplate($normalized, $watermark, $side);
            }
        } finally {
            @unlink($source);
            if ($normalized) {
                @unlink($normalized);
            }
        }
    }

    private function watermarkPdfTemplate(string $source, string $watermark, string $side): string
    {
        $pdf = new class extends Fpdi
        {
            private float $angle = 0;

            public function rotate(float $angle, float $x = -1, float $y = -1): void
            {
                if ($x === -1.0) {
                    $x = $this->x;
                }

                if ($y === -1.0) {
                    $y = $this->y;
                }

                if ($this->angle !== 0.0) {
                    $this->_out('Q');
                }

                $this->angle = $angle;

                if ($angle === 0.0) {
                    return;
                }

                $angle *= M_PI / 180;
                $c = cos($angle);
                $s = sin($angle);
                $cx = $x * $this->k;
                $cy = ($this->h - $y) * $this->k;

                $this->_out(sprintf(
                    'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
                    $c,
                    $s,
                    -$s,
                    $c,
                    $cx,
                    $cy,
                    -$cx,
                    -$cy
                ));
            }

            public function rotatedText(float $x, float $y, string $text, float $angle): void
            {
                $this->rotate($angle, $x, $y);
                $this->Text($x, $y, $text);
                $this->rotate(0);
            }
        };

        $pageCount = $pdf->setSourceFile($source);

        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);
            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetTextColor(90, 90, 90);
            $watermarkY = (($size['height'] - $pdf->GetStringWidth($watermark)) / 2) + 150;
            $watermarkX = $side === 'right' ? $size['width'] - 4 : 4;
            $pdf->rotatedText($watermarkX, max(8, $watermarkY), $watermark, 90);
        }

        return $pdf->Output('S');
    }

    private function normalizePdfForFpdi(string $source): string
    {
        $output = tempnam(sys_get_temp_dir(), 'document-normalized-');

        foreach ($this->ghostscriptCandidates() as $binary) {
            $process = new Process([
                $binary,
                '-dSAFER',
                '-dBATCH',
                '-dNOPAUSE',
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                '-o',
                $output,
                $source,
            ]);
            $process->run();

            if ($process->isSuccessful() && filesize($output) > 0) {
                return $output;
            }
        }

        @unlink($output);

        throw new \RuntimeException('Ghostscript PDF normalization failed.');
    }

    private function ghostscriptCandidates(): array
    {
        return array_values(array_filter([
            env('GHOSTSCRIPT_BINARY'),
            '/opt/homebrew/bin/gs',
            '/usr/local/bin/gs',
            trim((string) shell_exec('which gs 2>/dev/null')),
        ], fn ($binary) => is_string($binary) && $binary !== '' && is_executable($binary)));
    }

    public function destroy(Document $document): RedirectResponse
    {
        $disk = Storage::disk($document->disk);

        if ($disk->exists($document->path)) {
            $disk->delete($document->path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully.');
    }
}
