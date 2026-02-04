<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WikiSeeder extends Seeder
{
    /**
     * Seed the wiki with basic documentation.
     */
    public function run(): void
    {
        // Create categories
        $categories = [
            [
                'name' => 'Getting Started',
                'slug' => 'getting-started',
                'description' => 'New player guides and tutorials',
                'order' => 1,
            ],
            [
                'name' => 'Game Mechanics',
                'slug' => 'game-mechanics',
                'description' => 'Core gameplay systems explained',
                'order' => 2,
            ],
            [
                'name' => 'Features',
                'slug' => 'features',
                'description' => 'Game features and plugins',
                'order' => 3,
            ],
            [
                'name' => 'Economy',
                'slug' => 'economy',
                'description' => 'Money, banking, and trading',
                'order' => 4,
            ],
            [
                'name' => 'Combat',
                'slug' => 'combat',
                'description' => 'Fighting and combat systems',
                'order' => 5,
            ],
            [
                'name' => 'Social',
                'slug' => 'social',
                'description' => 'Gangs, chat, and community features',
                'order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('wiki_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Get category IDs
        $categoryIds = DB::table('wiki_categories')->pluck('id', 'slug');

        // Create pages
        $pages = [
            // Getting Started
            [
                'category_id' => $categoryIds['getting-started'],
                'title' => 'Welcome to the Game',
                'slug' => 'welcome',
                'content' => $this->getWelcomeContent(),
                'order' => 1,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['getting-started'],
                'title' => 'Your First Steps',
                'slug' => 'first-steps',
                'content' => $this->getFirstStepsContent(),
                'order' => 2,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['getting-started'],
                'title' => 'Understanding Stats',
                'slug' => 'understanding-stats',
                'content' => $this->getStatsContent(),
                'order' => 3,
                'is_published' => true,
            ],

            // Game Mechanics
            [
                'category_id' => $categoryIds['game-mechanics'],
                'title' => 'Experience & Leveling',
                'slug' => 'experience-leveling',
                'content' => $this->getExperienceContent(),
                'order' => 1,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['game-mechanics'],
                'title' => 'Energy System',
                'slug' => 'energy-system',
                'content' => $this->getEnergyContent(),
                'order' => 2,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['game-mechanics'],
                'title' => 'Cooldowns & Timers',
                'slug' => 'cooldowns-timers',
                'content' => $this->getCooldownsContent(),
                'order' => 3,
                'is_published' => true,
            ],

            // Features
            [
                'category_id' => $categoryIds['features'],
                'title' => 'Crimes',
                'slug' => 'crimes',
                'content' => $this->getCrimesContent(),
                'order' => 1,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['features'],
                'title' => 'Grand Theft Auto',
                'slug' => 'gta',
                'content' => $this->getGTAContent(),
                'order' => 2,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['features'],
                'title' => 'Missions',
                'slug' => 'missions',
                'content' => $this->getMissionsContent(),
                'order' => 3,
                'is_published' => true,
            ],

            // Economy
            [
                'category_id' => $categoryIds['economy'],
                'title' => 'Banking',
                'slug' => 'banking',
                'content' => $this->getBankingContent(),
                'order' => 1,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['economy'],
                'title' => 'Properties',
                'slug' => 'properties',
                'content' => $this->getPropertiesContent(),
                'order' => 2,
                'is_published' => true,
            ],

            // Combat
            [
                'category_id' => $categoryIds['combat'],
                'title' => 'PvP Combat',
                'slug' => 'pvp-combat',
                'content' => $this->getPvPContent(),
                'order' => 1,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['combat'],
                'title' => 'Jail & Hospital',
                'slug' => 'jail-hospital',
                'content' => $this->getJailHospitalContent(),
                'order' => 2,
                'is_published' => true,
            ],

            // Social
            [
                'category_id' => $categoryIds['social'],
                'title' => 'Gangs',
                'slug' => 'gangs',
                'content' => $this->getGangsContent(),
                'order' => 1,
                'is_published' => true,
            ],
            [
                'category_id' => $categoryIds['social'],
                'title' => 'Chat System',
                'slug' => 'chat-system',
                'content' => $this->getChatContent(),
                'order' => 2,
                'is_published' => true,
            ],
        ];

        foreach ($pages as $page) {
            DB::table('wiki_pages')->updateOrInsert(
                ['slug' => $page['slug']],
                array_merge($page, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Wiki seeded with ' . count($categories) . ' categories and ' . count($pages) . ' pages.');
    }

    private function getWelcomeContent(): string
    {
        return <<<'CONTENT'
# Welcome to the Game!

Welcome, new player! This persistent browser-based game (PBBG) puts you in the shoes of a rising criminal in an underground world of crime, money, and power.

## What is this game?

This is a text-based strategy game where you:
- Commit crimes to earn money and experience
- Build your criminal empire through various activities
- Fight other players for dominance
- Join or create gangs with other players
- Rise through the ranks to become the most powerful criminal

## Getting Started

1. **Check your stats** - Visit your profile to see your current attributes
2. **Commit your first crime** - Start small and work your way up
3. **Manage your energy** - Actions cost energy, so use it wisely
4. **Bank your money** - Keep your cash safe from theft
5. **Join a gang** - Find allies to help you rise to the top

Good luck on your journey to the top!
CONTENT;
    }

    private function getFirstStepsContent(): string
    {
        return <<<'CONTENT'
# Your First Steps

Starting out can be overwhelming. Here's a guide to your first few hours in the game.

## Step 1: Commit Petty Crimes

Navigate to the **Crimes** section and start with smaller crimes. These have:
- Lower rewards
- Higher success rates
- Shorter cooldowns

As you gain experience, you'll unlock more lucrative crimes.

## Step 2: Bank Your Earnings

After each successful crime, deposit your cash in the **Bank**. Money in your pocket can be stolen by other players!

## Step 3: Check Daily Rewards

Visit the game daily to collect your **Daily Reward**. Consecutive days give better bonuses!

## Step 4: Explore Features

Once you have some cash, explore:
- **Inventory** - Buy items and equipment
- **Properties** - Invest in real estate
- **Missions** - Complete tasks for rewards
- **Forum** - Meet other players

## Step 5: Join a Gang

Find a gang that's recruiting new members. Gangs provide:
- Protection
- Organized crime opportunities
- Community and chat
- Shared resources
CONTENT;
    }

    private function getStatsContent(): string
    {
        return <<<'CONTENT'
# Understanding Your Stats

Your character has several important statistics that affect gameplay.

## Core Stats

| Stat | Description |
|------|-------------|
| **Health** | Your hit points. Reach 0 and you'll be hospitalized |
| **Energy** | Required for most actions. Regenerates over time |
| **Cash** | Money in your pocket (can be stolen) |
| **Bank** | Money safely stored (cannot be stolen) |

## Combat Stats

| Stat | Effect |
|------|--------|
| **Strength** | Increases attack damage |
| **Defense** | Reduces incoming damage |
| **Speed** | Affects who attacks first |

## Progression Stats

| Stat | Description |
|------|-------------|
| **Level** | Your overall progression |
| **Experience** | Points toward next level |
| **Rank** | Your criminal title |
| **Respect** | Earned through various activities |

## Improving Stats

- **Level up** by gaining experience
- **Train** at the gym to improve combat stats
- **Equip items** for stat bonuses
- **Complete missions** for permanent boosts
CONTENT;
    }

    private function getExperienceContent(): string
    {
        return <<<'CONTENT'
# Experience & Leveling

Experience (XP) is the primary progression currency in the game.

## Earning Experience

You gain XP from:
- Committing crimes (varies by crime type)
- Winning fights
- Completing missions
- Organized crimes
- Various other activities

## Leveling Up

When you accumulate enough XP, you'll level up automatically. Each level:
- Increases your maximum health
- Increases your maximum energy
- May unlock new features
- May unlock new crimes

## Rank System

Separate from levels, your **Rank** represents your criminal standing:

1. Thug
2. Punk
3. Hustler
4. Gangster
5. Hitman
6. Boss
7. Godfather
8. *And more...*

Ranks unlock:
- New crime opportunities
- Respect from other players
- Special features
CONTENT;
    }

    private function getEnergyContent(): string
    {
        return <<<'CONTENT'
# Energy System

Energy is your action resource. Most activities require energy to perform.

## Energy Basics

- **Maximum Energy**: Starts at 100, increases with level
- **Regeneration**: Restores naturally over time
- **Cost**: Different actions cost different amounts

## Energy Costs

| Action | Typical Cost |
|--------|--------------|
| Petty Crime | 5 energy |
| Major Crime | 10-15 energy |
| GTA | 10 energy |
| Attack Player | 15 energy |
| Train | 10 energy |

## Restoring Energy

Ways to restore energy faster:
- **Wait** - Energy regenerates every few minutes
- **Energy Drinks** - Buy from the store
- **Premium Items** - Special refills
- **Daily Rewards** - Often include energy bonuses

## Tips

- Don't let energy cap out - you're wasting regeneration
- Plan your activities around energy costs
- Save energy for important actions
CONTENT;
    }

    private function getCooldownsContent(): string
    {
        return <<<'CONTENT'
# Cooldowns & Timers

Many actions have cooldowns - waiting periods before you can repeat them.

## Common Cooldowns

| Action | Typical Cooldown |
|--------|-----------------|
| Crimes | 60-120 seconds |
| GTA | 180 seconds |
| Attack | 60 seconds |
| Travel | Varies by distance |

## Active Timers

You can see your active timers on your dashboard. They show:
- Time remaining
- What action is on cooldown
- When you can act again

## Jail & Hospital Timers

Special timers that affect gameplay:
- **Jail**: Can't perform most actions while jailed
- **Hospital**: Can't perform most actions while hospitalized

You can reduce these with:
- Bail/Bustout (jail)
- Medical treatment (hospital)
- Premium items
CONTENT;
    }

    private function getCrimesContent(): string
    {
        return <<<'CONTENT'
# Crimes

Crimes are the bread and butter of earning money and experience.

## Crime Types

Different crime categories based on risk/reward:

### Petty Crimes
- Pickpocketing
- Shoplifting
- Vandalism
- *Low risk, low reward*

### Serious Crimes
- Robbery
- Burglary
- Fraud
- *Medium risk, medium reward*

### Major Crimes
- Bank Heist
- Kidnapping
- Arms Dealing
- *High risk, high reward*

## Success Factors

Your success rate depends on:
- Your level
- Crime difficulty
- Your stats
- Equipment bonuses

## Rewards

Successful crimes give:
- Cash (varies widely)
- Experience points
- Occasionally items
- Achievement progress

## Failure

Failed crimes may result in:
- No reward
- Jail time
- Health loss
- Bounty increase
CONTENT;
    }

    private function getGTAContent(): string
    {
        return <<<'CONTENT'
# Grand Theft Auto

Steal vehicles for cash or add them to your garage!

## How It Works

1. Navigate to the GTA section
2. Choose a location to search
3. Attempt to steal a vehicle
4. Success = vehicle or cash!

## Locations

Different locations have different vehicle types:
- **Streets**: Common cars
- **Parking Lots**: Mixed vehicles
- **Dealerships**: Luxury vehicles
- **Docks**: Commercial vehicles

## Outcomes

When you successfully steal a vehicle:
- **Keep it**: Add to your garage (if you have space)
- **Sell it**: Immediate cash payout

## Garages

Store your stolen vehicles in garages:
- Upgrade to hold more vehicles
- Some vehicles increase in value
- Use vehicles for racing or transport

## Tips

- Higher level = better vehicles
- Some locations require minimum level
- Failed attempts may result in jail
CONTENT;
    }

    private function getMissionsContent(): string
    {
        return <<<'CONTENT'
# Missions

Missions are multi-step tasks that reward you upon completion.

## Mission Types

### Story Missions
- Follow the main narrative
- Unlock progressively
- Major rewards

### Daily Missions
- Reset every 24 hours
- Smaller, quick tasks
- Consistent rewards

### Weekly Missions
- Reset every week
- Larger challenges
- Better rewards

## Completing Missions

Each mission has objectives like:
- Commit X crimes
- Earn X amount of cash
- Win X fights
- Travel to X location

Track your progress in the Missions menu.

## Rewards

Missions can give:
- Cash
- Experience
- Items
- Stat bonuses
- Achievements
- Unique titles
CONTENT;
    }

    private function getBankingContent(): string
    {
        return <<<'CONTENT'
# Banking

The bank keeps your money safe from theft.

## Bank Features

### Deposits
- Transfer cash from pocket to bank
- No fees for deposits
- Instant transfer

### Withdrawals
- Transfer bank balance to pocket
- May have daily limits
- Instant access

### Interest
- Earn interest on your balance
- Paid out periodically
- Rate depends on total balance

## Money Safety

**Cash in pocket** can be:
- Stolen by other players
- Lost on death (percentage)
- Taken when mugged

**Cash in bank** is:
- 100% safe from theft
- Protected from death
- Secure from all attacks

## Tips

- Bank frequently
- Only carry what you need
- Check interest rates
- Use transfers between players through bank
CONTENT;
    }

    private function getPropertiesContent(): string
    {
        return <<<'CONTENT'
# Properties

Invest in real estate for passive income!

## Property Types

| Type | Income | Cost |
|------|--------|------|
| Small House | Low | $ |
| Apartment | Low-Med | $$ |
| Business | Medium | $$$ |
| Warehouse | Med-High | $$$$ |
| Mansion | High | $$$$$ |

## Buying Properties

1. Visit the Properties section
2. Browse available properties
3. Purchase with cash
4. Start collecting income!

## Income Collection

Properties generate income over time:
- Income accumulates hourly
- Collect manually or auto-collect
- Higher properties = more income

## Property Management

- **Upgrade**: Improve income generation
- **Sell**: Cash out your investment
- **Rent**: Some properties can be rented to NPCs

## Strategy

- Start small, reinvest profits
- Diversify your portfolio
- Location matters for some properties
- Check ROI before buying
CONTENT;
    }

    private function getPvPContent(): string
    {
        return <<<'CONTENT'
# PvP Combat

Attack other players to steal their cash and earn respect!

## Starting a Fight

1. Find a target (Attack List, Search, etc.)
2. Check their stats and level
3. Initiate the attack
4. Combat resolves automatically

## Combat Mechanics

Combat is turn-based:
1. Speed determines who attacks first
2. Strength affects damage dealt
3. Defense reduces damage taken
4. Health determines the winner

## Winning

Victory rewards:
- Portion of target's cash
- Experience points
- Respect points
- Achievement progress

## Losing

Defeat consequences:
- Health reduced (possibly hospitalized)
- No rewards
- Attacker may take your cash

## Tips

- Check stats before attacking
- Equip weapons and armor
- Target players near your level
- Watch your health before fighting
- Join a gang for protection
CONTENT;
    }

    private function getJailHospitalContent(): string
    {
        return <<<'CONTENT'
# Jail & Hospital

Sometimes your criminal activities have consequences!

## Jail

You can be jailed for:
- Failed crimes
- Getting caught by police
- Other player actions

### While Jailed
- Most actions unavailable
- Timer counts down
- Can be busted out

### Getting Out
- **Wait**: Timer expires
- **Bail**: Pay cash to leave early
- **Bustout**: Gang member frees you

## Hospital

You're hospitalized when:
- Health reaches 0
- Losing a fight
- Certain events

### While Hospitalized
- Most actions unavailable
- Health regenerates faster
- Timer counts down

### Recovery
- **Wait**: Full recovery time
- **Pay**: Medical bills for early release
- **Items**: Health kits reduce time

## Prevention

- Keep health high
- Avoid risky fights
- Bank your cash
- Join a protective gang
CONTENT;
    }

    private function getGangsContent(): string
    {
        return <<<'CONTENT'
# Gangs

Join forces with other players!

## Gang Benefits

- **Protection**: Gang members defend each other
- **Organized Crime**: Group heists for big payouts
- **Chat**: Private gang communication
- **Territory**: Control areas of the map
- **Bonuses**: Gang perks and upgrades

## Joining a Gang

1. Browse open gangs
2. Apply to one that fits you
3. Wait for leader approval
4. Start contributing!

## Creating a Gang

Requirements:
- Minimum level
- Creation fee
- Gang name (unique)

As a leader you can:
- Invite/kick members
- Set ranks and permissions
- Manage gang bank
- Declare wars

## Organized Crime

Gang-exclusive heists:
- Require multiple participants
- Bigger rewards
- Shared payouts
- Experience for all

## Gang Wars

Gangs can declare war:
- Attack enemy members freely
- Capture territory
- Earn war points
- Victory = rewards
CONTENT;
    }

    private function getChatContent(): string
    {
        return <<<'CONTENT'
# Chat System

Communicate with other players in real-time!

## Chat Types

### Global Chat
- Everyone can see/post
- General discussion
- Moderated by staff

### Gang Chat
- Private to your gang
- Coordinate activities
- Share strategies

### Direct Messages
- Private 1-on-1 chat
- Send to any player
- Conversation history

## Chat Features

- **Emojis**: Express yourself 😎
- **Mentions**: @username to notify
- **Links**: Share URLs (filtered)
- **Reactions**: React to messages

## Chat Rules

1. Be respectful
2. No spamming
3. No real-world threats
4. No advertising
5. English in global chat

## Reporting

See something wrong?
- Click report button on message
- Staff will review
- Violators are punished

Stay connected and make allies!
CONTENT;
    }
}
