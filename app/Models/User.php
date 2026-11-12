<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const CREATOR = 'creator';
    public const OFFICER = 'officer';
    public const RECEPTIONIST = 'receptionist';
    public const ROLES = [self::CREATOR, self::OFFICER, self::RECEPTIONIST];

    protected $fillable = ['first_name', 'middle_name', 'email', 'contact', 'alt_contact', 'role', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->middle_name);
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(mb_substr($this->first_name, 0, 1).mb_substr($this->middle_name, 0, 1));
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isCreator(): bool { return $this->role === self::CREATOR; }
    public function isOfficer(): bool { return $this->role === self::OFFICER; }
    public function isReceptionist(): bool { return $this->role === self::RECEPTIONIST; }

    /** Receptionists chat with officers/creators; creators also chat with officers. */
    public function canChatWith(User $other): bool
    {
        if ($other->id === $this->id) {
            return false;
        }
        return $this->isReceptionist() !== $other->isReceptionist()
            || ($this->isCreator() && $other->isOfficer())
            || ($this->isOfficer() && $other->isCreator());
    }

    public function chatContacts()
    {
        return User::where('id', '!=', $this->id)->orderBy('first_name')->get()
            ->filter(fn (User $u) => $this->canChatWith($u))->values();
    }

    public function unreadMessageCount(): int
    {
        return Message::where('recipient_id', $this->id)->where('recipient_deleted', false)->whereNull('read_at')->count();
    }
}
