<?php

use App\Jobs\SyncSchoolToMeiliSearch;
use App\Models\School;

class SchoolObserver
{
    public function created(School $school) {
      SyncSchoolToMeiliSearch::dispatch($school, 'created');
    }
      
    public function updated(School $school) {
      SyncSchoolToMeiliSearch::dispatch($school, 'updated');
    }

    public function deleted(School $school) {
      SyncSchoolToMeiliSearch::dispatch($school, 'deleted');
    }
}