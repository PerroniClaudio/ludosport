<?php

use App\Jobs\SyncAcademyToMeiliSearch;
use App\Models\Academy;

class AcademyObserver
{
    public function created(Academy $academy) {
      SyncAcademyToMeiliSearch::dispatch($academy, 'created');
    }
      
    public function updated(Academy $academy) {
      SyncAcademyToMeiliSearch::dispatch($academy, 'updated');
    }

    public function deleted(Academy $academy) {
      SyncAcademyToMeiliSearch::dispatch($academy, 'deleted');
    }
}