<?php

use App\Models\Document;
use App\Models\DocumentEvent;
use App\Models\Nation;
use App\Models\PrivacyPolicy;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['scout.driver' => null]);
    Rank::create(['name' => 'Novice']);
    Nation::create(['name' => 'Italy', 'code' => 'IT']);
    PrivacyPolicy::getOrCreate();
    Role::create(['name' => 'admin', 'prefix' => 'admin', 'label' => 'admin']);
    Role::create(['name' => 'athlete', 'prefix' => 'athlete', 'label' => 'athlete']);
    Role::create(['name' => 'technician', 'prefix' => 'technician', 'label' => 'technician']);
});

function makeUserWithRole(string $roleName): User
{
    $user = User::factory()->create([
        'nation_id' => 1,
        'privacy_policy_accepted_at' => now(),
    ]);

    $role = Role::where('name', $roleName)->firstOrFail();
    $user->roles()->attach($role);

    return $user;
}

function testPdfBytes(): string
{
    $pdf = new FPDF;
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(40, 10, 'Test PDF');

    return $pdf->Output('S');
}

test('admin sees documents page', function () {
    $admin = makeUserWithRole('admin');

    $response = $this->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->get(route('documents.index'));

    $response->assertOk();
    $response->assertSee('documents/create');
});

test('admin sees document upload form', function () {
    $admin = makeUserWithRole('admin');

    $response = $this->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->get(route('documents.create'));

    $response->assertOk();
    $response->assertSee('Upload a PDF Document');
    $response->assertSee('Watermark information');
});

test('admin uploads valid pdf document', function () {
    Storage::fake('gcs');
    $admin = makeUserWithRole('admin');

    $response = $this->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->post(route('documents.store'), [
            'document' => UploadedFile::fake()->create('manual.pdf', 120, 'application/pdf'),
            'watermark_fields' => ['email', 'user_id'],
            'watermark_side' => 'right',
        ]);

    $response->assertRedirect(route('documents.index', absolute: false));

    $document = Document::first();

    expect($document)->not->toBeNull();
    expect($document->original_name)->toBe('manual.pdf');
    expect($document->mime_type)->toBe('application/pdf');
    expect($document->uploaded_by)->toBe($admin->id);
    expect($document->watermark_fields)->toBe(['email', 'user_id']);
    expect($document->watermark_side)->toBe('right');

    Storage::disk('gcs')->assertExists($document->path);
});

test('admin cannot upload non pdf document', function () {
    Storage::fake('gcs');
    $admin = makeUserWithRole('admin');

    $response = $this->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->post(route('documents.store'), [
            'document' => UploadedFile::fake()->create('manual.txt', 10, 'text/plain'),
        ]);

    $response->assertSessionHasErrors('document');
    expect(Document::count())->toBe(0);
});

test('admin downloads existing document', function () {
    Storage::fake('gcs');
    Storage::disk('gcs')->buildTemporaryUrlsUsing(function (string $path) {
        return 'https://example.test/temp/'.$path;
    });

    $admin = makeUserWithRole('admin');
    $path = 'documents/2026/07/manual.pdf';
    Storage::disk('gcs')->put($path, testPdfBytes());

    $document = Document::create([
        'original_name' => 'manual.pdf',
        'stored_name' => 'manual.pdf',
        'path' => $path,
        'disk' => 'gcs',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size_bytes' => 8,
        'uploaded_by' => $admin->id,
        'watermark_fields' => ['email'],
        'watermark_side' => 'right',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->get(route('documents.download', $document));

    $location = $response->headers->get('Location');
    $expectedPrefix = 'documents/'.$document->id.'/downloads/'.$admin->id.'/';

    expect($location)
        ->toStartWith('https://example.test/temp/'.$expectedPrefix)
        ->not->toBe('https://example.test/temp/'.$path);

    Storage::disk('gcs')->assertExists(Str::after($location, 'https://example.test/temp/'));

    expect(DocumentEvent::where('event_type', 'document_downloaded')->where('operation_result', 'success')->exists())->toBeTrue();
});

test('admin deletes existing document', function () {
    Storage::fake('gcs');
    $admin = makeUserWithRole('admin');
    $path = 'documents/2026/07/manual.pdf';
    Storage::disk('gcs')->put($path, 'fake-pdf');

    $document = Document::create([
        'original_name' => 'manual.pdf',
        'stored_name' => 'manual.pdf',
        'path' => $path,
        'disk' => 'gcs',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size_bytes' => 8,
        'uploaded_by' => $admin->id,
        'watermark_fields' => ['email'],
        'watermark_side' => 'left',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->delete(route('documents.destroy', $document));

    $response->assertRedirect(route('documents.index', absolute: false));
    expect(Document::count())->toBe(0);
    Storage::disk('gcs')->assertMissing($path);
});

test('non admin sees documents page without admin actions', function () {
    Storage::fake('gcs');
    $user = makeUserWithRole('technician');

    $document = Document::create([
        'original_name' => 'manual.pdf',
        'stored_name' => 'manual.pdf',
        'path' => 'documents/2026/07/manual.pdf',
        'disk' => 'gcs',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size_bytes' => 8,
        'uploaded_by' => $user->id,
        'watermark_fields' => ['email'],
        'watermark_side' => 'left',
    ]);

    $response = $this->actingAs($user)->withSession(['role' => 'technician'])
        ->get(route('documents.index'));

    $response->assertOk();
    $response->assertSee('manual.pdf');
    $response->assertSee('Terms of Access');
    $response->assertSee('accept-terms');
    $response->assertDontSee('documents/create');
    $response->assertDontSee('Delete');

    $event = DocumentEvent::where('event_type', 'reserved_area_accessed')->first();

    expect($event)->not->toBeNull();
    expect($event->user_id)->toBe($user->id);
    expect($event->user_name)->toBe(trim($user->name.' '.$user->surname));
    expect($event->document_id)->toBe($document->id);
    expect($event->terms_version)->toBe('v1');
    expect($event->operation_result)->toBe('success');
    expect($event->ip_address)->not->toBeNull();
    expect($event->session_id)->not->toBeNull();
});

test('non admin terms view is logged', function () {
    Storage::fake('gcs');
    $user = makeUserWithRole('technician');

    $document = Document::create([
        'original_name' => 'manual.pdf',
        'stored_name' => 'manual.pdf',
        'path' => 'documents/2026/07/manual.pdf',
        'disk' => 'gcs',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size_bytes' => 8,
        'uploaded_by' => $user->id,
        'watermark_fields' => ['email'],
        'watermark_side' => 'left',
    ]);

    $this->actingAs($user)->withSession(['role' => 'technician'])
        ->post(route('documents.terms-viewed', $document))
        ->assertOk();

    expect(DocumentEvent::where('event_type', 'terms_viewed')
        ->where('document_id', $document->id)
        ->where('user_id', $user->id)
        ->where('operation_result', 'success')
        ->exists())->toBeTrue();
});

test('non admin accepts terms and downloads watermarked document', function () {
    Storage::fake('gcs');
    Storage::disk('gcs')->buildTemporaryUrlsUsing(function (string $path) {
        return 'https://example.test/temp/'.$path;
    });

    $user = makeUserWithRole('technician');
    $path = 'documents/2026/07/manual.pdf';
    Storage::disk('gcs')->put($path, testPdfBytes());

    $document = Document::create([
        'original_name' => 'manual.pdf',
        'stored_name' => 'manual.pdf',
        'path' => $path,
        'disk' => 'gcs',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size_bytes' => 8,
        'uploaded_by' => $user->id,
        'watermark_fields' => ['email'],
        'watermark_side' => 'left',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['role' => 'technician'])
        ->post(route('documents.accept-terms', $document), [
            'terms_accepted' => '1',
        ]);

    $location = $response->headers->get('Location');
    $expectedPrefix = 'documents/'.$document->id.'/downloads/'.$user->id.'/';

    expect($location)
        ->toStartWith('https://example.test/temp/'.$expectedPrefix)
        ->not->toBe('https://example.test/temp/'.$path);

    Storage::disk('gcs')->assertExists(Str::after($location, 'https://example.test/temp/'));

    expect(DocumentEvent::where('event_type', 'terms_accepted')->where('document_id', $document->id)->exists())->toBeTrue();
    expect(DocumentEvent::where('event_type', 'document_downloaded')->where('document_id', $document->id)->where('operation_result', 'success')->exists())->toBeTrue();
});

test('non admin cannot access admin document routes', function () {
    Storage::fake('gcs');
    $user = makeUserWithRole('technician');

    $document = Document::create([
        'original_name' => 'manual.pdf',
        'stored_name' => 'manual.pdf',
        'path' => 'documents/2026/07/manual.pdf',
        'disk' => 'gcs',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size_bytes' => 8,
        'uploaded_by' => $user->id,
        'watermark_fields' => ['email'],
        'watermark_side' => 'left',
    ]);

    $this->actingAs($user)->withSession(['role' => 'technician'])
        ->get(route('documents.create'))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($user)->withSession(['role' => 'technician'])
        ->post(route('documents.store'), [
            'document' => UploadedFile::fake()->create('manual.pdf', 120, 'application/pdf'),
            'watermark_fields' => ['email'],
            'watermark_side' => 'left',
        ])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($user)->withSession(['role' => 'technician'])
        ->get(route('documents.download', $document))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($user)->withSession(['role' => 'technician'])
        ->delete(route('documents.destroy', $document))
        ->assertRedirect(route('dashboard', absolute: false));
});
