<?php
namespace App\Jobs;

use App\Models\Academy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MeiliSearch\Client;

class SyncAcademyToMeiliSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $academy;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Academy $academy, String $type)
    {
        $this->academy = $academy;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
        $index = $client->index('academies');

        if(in_array($this->type, ['created', 'updated'])) {
            $index->updateDocuments([$this->academy->toSearchableArray()]);
        } 
        if  ($this->type === 'deleted') {
            $index->deleteDocument($this->academy->id);
        }
    }
}
