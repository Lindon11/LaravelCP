# Gangster Legends V2-Style Rank System Implementation

## Overview
Implemented complete rank progression system based on Gangster Legends V2 architecture. Ranks are now **experience-based** rather than level-based, with automatic progression, rewards, and health scaling.

## Key Features

### 1. **Experience-Based Progression**
- Ranks advance based on **total experience** earned (not character level)
- 10 ranks from Thug (0 exp) to Godfather (125,000 exp)
- Users gain experience from crimes, theft, missions, achievements

### 2. **Automatic Rank Checking**
- `CheckUserRank` middleware runs on every authenticated request
- Automatically checks if user qualifies for next rank
- Awards rewards instantly when ranking up
- Updates max_health based on new rank

### 3. **Rank Rewards**
Each rank grants rewards when reached:
- **Cash**: From $5,000 (Hustler) to $6,000,000 (Godfather)
- **Bullets**: From 50 (Hustler) to 15,000 (Godfather)
- **Max Health**: From 100 HP (Thug) to 2,500 HP (Godfather)

### 4. **Rank Limiting** (Optional)
- Ranks can have user limits (e.g., only 10 Godfathers allowed)
- Set `user_limit` to 0 for unlimited
- Users cannot rank up if limit reached

### 5. **Notification System**
- Automatic notification when ranking up
- Shows rank name and rewards received
- Stored in `notifications` table

## Database Structure

### Ranks Table
```sql
- id (primary key)
- name (string) - Rank name
- required_exp (integer) - Experience needed
- max_health (integer) - Max HP at this rank
- cash_reward (integer) - Cash awarded
- bullet_reward (integer) - Bullets awarded
- user_limit (integer) - Max users (0 = unlimited)
```

### Users Table (Added Fields)
```sql
- rank_id (foreign key) - Current rank
- rank (string) - Rank name (kept for compatibility)
```

## Rank Progression Table

| Rank | Experience | Max Health | Cash Reward | Bullet Reward |
|------|-----------|-----------|-------------|---------------|
| Thug | 0 | 100 | $0 | 0 |
| Hustler | 100 | 150 | $5,000 | 50 |
| Gangster | 500 | 200 | $25,000 | 100 |
| Enforcer | 1,500 | 300 | $75,000 | 250 |
| Capo | 3,500 | 450 | $150,000 | 500 |
| Underboss | 7,500 | 650 | $350,000 | 1,000 |
| Consigliere | 15,000 | 900 | $750,000 | 2,000 |
| Boss | 30,000 | 1,250 | $1,500,000 | 4,000 |
| Don | 60,000 | 1,750 | $3,000,000 | 8,000 |
| Godfather | 125,000 | 2,500 | $6,000,000 | 15,000 |

## Implementation Details

### User Model Methods

**`checkRank()`**
```php
// Automatically called by middleware
// Returns true if rank changed
$user->checkRank();
```

**Attributes**
```php
$user->current_rank      // Rank model
$user->next_rank         // Next rank model
$user->exp_progress      // Progress percentage (0-100)
```

### Frontend Integration

Rank data is shared via Inertia:
```javascript
const { user } = usePage().props.auth;

// Access rank data
user.current_rank.name           // "Hustler"
user.current_rank.max_health     // 150
user.next_rank.name              // "Gangster"
user.next_rank.required_exp      // 500
user.exp_progress                // 45.5 (percentage)
```

### Admin Panel

Filament resource at `/admin/ranks` allows:
- View all ranks with user counts
- Create new ranks
- Edit experience requirements
- Set rewards and health
- Configure user limits
- Delete ranks (except first)

## V2 Compatibility

This system closely mirrors V2's implementation:

| V2 Feature | V3 Implementation |
|------------|-------------------|
| `checkRank()` on page load | Middleware auto-check |
| `R_exp` field | `required_exp` field |
| `R_health` field | `max_health` field |
| `R_cashReward` | `cash_reward` |
| `R_bulletReward` | `bullet_reward` |
| `R_limit` | `user_limit` |
| `US_rank` FK | `rank_id` FK |
| Rank-based health | Auto-updated on rank up |
| Reward notifications | Laravel notifications |

## Usage Examples

### Award Experience
```php
// Award experience (auto-checks rank)
$user->experience += 50;
$user->save();
```

### Manual Rank Check
```php
// Force rank check
if ($user->checkRank()) {
    // User ranked up!
}
```

### Get Rank Info
```php
$rank = $user->currentRank;
$rank->name;          // "Enforcer"
$rank->max_health;    // 300
$rank->cash_reward;   // 75000
```

### Check Next Rank
```php
$next = $user->next_rank;
if ($next) {
    $expNeeded = $next->required_exp - $user->experience;
    echo "Need {$expNeeded} more exp";
}
```

## Configuration

### Add More Ranks
Add to `RanksTableSeeder`:
```php
['name' => 'Supreme Leader', 'required_exp' => 250000, 
 'max_health' => 5000, 'cash_reward' => 10000000, 
 'bullet_reward' => 25000, 'user_limit' => 1],
```

### Adjust Experience Rewards
When awarding experience, the rank check happens automatically:
```php
$user->experience += $amount;
$user->save(); // Middleware will check rank on next request
```

### Disable Auto-Check
Remove from `bootstrap/app.php`:
```php
// Remove this line:
\App\Http\Middleware\CheckUserRank::class,
```

## Files Changed

### Models
- `app/Models/Rank.php` - Added V2 methods
- `app/Models/User.php` - Added checkRank(), relationships, attributes

### Middleware
- `app/Http/Middleware/CheckUserRank.php` - Auto-check ranks
- `app/Http/Middleware/HandleInertiaRequests.php` - Share rank data

### Database
- `database/migrations/2026_01_28_152540_create_ranks_table.php` - V2 structure
- `database/migrations/2026_01_29_210702_add_rank_id_to_users_table.php` - FK
- `database/seeders/RankSeeder.php` - 10 default ranks
- `database/seeders/RanksTableSeeder.php` - Updated structure

### Admin
- `app/Filament/Resources/RankResource.php` - Admin CRUD interface

### Auth
- `app/Actions/Fortify/CreateNewUser.php` - Default rank on register
- `app/Http/Controllers/InstallerController.php` - Default rank for admin

## Testing

1. **Register New User**
   - Should start as "Thug" rank
   - rank_id = 1

2. **Award Experience**
   ```php
   $user->experience = 150;
   $user->save();
   ```
   - Next page load should rank up to "Hustler"
   - Should receive $5,000 and 50 bullets
   - Max health should increase to 150

3. **Check Admin Panel**
   - Visit `/admin/ranks`
   - View all ranks with user counts
   - Edit ranks and see changes immediately

4. **Multiple Rank Ups**
   ```php
   $user->experience = 10000;
   $user->save();
   ```
   - Should rank up multiple times in sequence
   - Should receive all intermediate rewards

## Notes

- Middleware runs on every request (minimal performance impact)
- Rank checks are recursive (handles multiple rank-ups)
- Max health auto-updated on rank progression
- Old `rank` string field kept for backward compatibility
- Experience from all sources (crimes, theft, missions) counts
- Notifications stored for rank-up history

## Next Steps

1. **Display Rank in UI**
   - Show current rank name in navbar
   - Add rank icon/badge
   - Display progress bar to next rank

2. **Leaderboards**
   - Add "Top Ranks" leaderboard
   - Show rank distribution statistics

3. **Rank-Based Features**
   - Unlock crimes by rank (not level)
   - Rank-based gang permissions
   - Rank-required items/equipment

4. **Prestige System**
   - Reset to Thug with bonuses
   - Prestige ranks with unique rewards

---

**Status**: ✅ Fully Implemented and Tested
**Based on**: Gangster Legends V2 (christopherday/Gangster-Legends-V2)
**Date**: January 29, 2026
