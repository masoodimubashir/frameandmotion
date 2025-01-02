<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{

  protected $fillable = ['client_id', 'booking_date', 'ceremony_date', 'created_by', 'updated_by'];

  /**
   * Get the client that owns the Booking
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }


  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class);
  }


}
