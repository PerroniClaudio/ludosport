<?php
namespace App\Jobs;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MeiliSearch\Client;

class SyncSchoolToMeiliSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $school;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(School $school, String $type)
    {
        $this->school = $school;
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
        $index = $client->index('schools');

        if(in_array($this->type, ['created', 'updated'])) {
            $index->updateDocuments([$this->school->toSearchableArray()]);
        } 
        if  ($this->type === 'deleted') {
            $index->deleteDocument($this->school->id);
        }
    }
}
