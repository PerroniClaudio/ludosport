<?php

use App\Jobs\SyncUserToMeiliSearch;
use App\Models\User;

class UserObserver
{
    public function created(User $user) {
      SyncUserToMeiliSearch::dispatch($user, 'created');
    }
      
    public function updated(User $user) {
      SyncUserToMeiliSearch::dispatch($user, 'updated');
    }

    public function deleted(User $user) {
      SyncUserToMeiliSearch::dispatch($user, 'deleted');
    }
}