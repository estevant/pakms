<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Report extends Model
{
    protected $table      = 'reports';
    protected $primaryKey = 'report_id';
    public    $timestamps = false;

    protected $fillable = [
      'reporter_id', 'target_type', 'target_id',
      'reason', 'description', 'status'
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id', 'user_id');
    }
}
