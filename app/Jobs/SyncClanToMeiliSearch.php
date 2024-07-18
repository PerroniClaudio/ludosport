<?php
namespace App\Jobs;

use App\Models\Clan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MeiliSearch\Client;

class SyncClanToMeiliSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $clan;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Clan $clan, String $type)
    {
        $this->clan = $clan;
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
        $index = $client->index('clans');

        if(in_array($this->type, ['created', 'updated'])) {
            $index->updateDocuments([$this->clan->toSearchableArray()]);
        } 
        if  ($this->type === 'deleted') {
            $index->deleteDocument($this->clan->id);
        }
    }
}
