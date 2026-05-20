<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('meeting.{code}', function ($user, $code) {
  return [
    'id' => $user->id,
    'name' => $user->name,
  ];
});
