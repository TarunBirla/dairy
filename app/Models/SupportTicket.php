<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'farmer_id',
        'category',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }
}
