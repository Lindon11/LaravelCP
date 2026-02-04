<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $fillable = [
        'type',
        'message',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'ip',
        'user_id',
        'user_agent',
        'context',
        'resolved',
        'count',
        'last_seen_at',
    ];

    protected $casts = [
        'context' => 'array',
        'resolved' => 'boolean',
        'last_seen_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function logError(\Throwable $exception, $request = null): void
    {
        $user = auth()->user();
        
        // Create a unique hash for this error to group duplicates
        $hash = md5($exception->getMessage() . $exception->getFile() . $exception->getLine());
        
        // Check if this error already exists
        $errorLog = static::where('type', get_class($exception))
            ->where('message', $exception->getMessage())
            ->where('file', $exception->getFile())
            ->where('line', $exception->getLine())
            ->first();

        if ($errorLog) {
            // Update existing error
            $errorLog->increment('count');
            $errorLog->update(['last_seen_at' => now()]);
        } else {
            // Create new error log
            static::create([
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'url' => $request ? $request->fullUrl() : null,
                'method' => $request ? $request->method() : null,
                'ip' => $request ? $request->ip() : null,
                'user_id' => $user?->id,
                'user_agent' => $request ? $request->userAgent() : null,
                'context' => $request ? [
                    'input' => $request->except(['password', 'password_confirmation']),
                    'headers' => $request->headers->all(),
                ] : null,
                'last_seen_at' => now(),
            ]);
        }
    }
}
