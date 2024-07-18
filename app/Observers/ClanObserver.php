<?php

use App\Jobs\SyncClanToMeiliSearch;
use App\Models\Clan;

class ClanObserver
{
    public function created(Clan $clan) {
      SyncClanToMeiliSearch::dispatch($clan, 'created');
    }
      
    public function updated(Clan $clan) {
      SyncClanToMeiliSearch::dispatch($clan, 'updated');
    }

    public function deleted(Clan $clan) {
      SyncClanToMeiliSearch::dispatch($clan, 'deleted');
    }
}