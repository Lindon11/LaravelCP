<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailSetting extends Model
{
    protected $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'mailgun_domain',
        'mailgun_secret',
        'mailgun_endpoint',
        'is_active',
        'last_tested_at',
        'test_successful',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
        'test_successful' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'mailgun_secret',
    ];

    /**
     * Encrypt password when setting
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when getting
     */
    public function getPasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Encrypt mailgun secret when setting
     */
    public function setMailgunSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['mailgun_secret'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt mailgun secret when getting
     */
    public function getMailgunSecretAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get the active email settings
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Apply these settings to Laravel's mail config at runtime
     */
    public function applyToConfig(): void
    {
        if ($this->mailer === 'mailgun') {
            config([
                'mail.default' => 'mailgun',
                'mail.mailers.mailgun' => [
                    'transport' => 'mailgun',
                ],
                'services.mailgun' => [
                    'domain' => $this->mailgun_domain,
                    'secret' => $this->mailgun_secret,
                    'endpoint' => $this->mailgun_endpoint ?: 'api.mailgun.net',
                    'scheme' => 'https',
                ],
                'mail.from' => [
                    'address' => $this->from_address,
                    'name' => $this->from_name,
                ],
            ]);
        } else {
            // SMTP settings
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $this->host,
                    'port' => $this->port,
                    'encryption' => $this->encryption,
                    'username' => $this->username,
                    'password' => $this->password,
                ],
                'mail.from' => [
                    'address' => $this->from_address,
                    'name' => $this->from_name,
                ],
            ]);
        }
    }
}
