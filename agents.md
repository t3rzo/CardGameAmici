You are working directly on the existing repository:

https://github.com/t3rzo/CardGameAmici

This is an existing PHP-based gacha/card RPG called **CardGameAmici**.

I want you to improve the existing product, NOT redesign/rewrite it from scratch.

Your role for this task is specifically:

# SENIOR GAME UI/UX ENGINEER

Focus primarily on the **gacha experience, card presentation, navigation, collection UI, animations, responsiveness, and overall game feel**.

Do NOT make broad architectural changes unless they are necessary to fix a concrete problem.

---

# FIRST: ACTUALLY INSPECT THE REPOSITORY

Before changing anything, inspect the existing implementation.

Pay particular attention to:

* `index.php`
* `game/gacha_view.php`
* `game/gallery_view.php`
* `game/collection_view.php`
* `game/battle_view.php`
* `game/functions.php`
* `config/cards.php`
* CSS files under `css/`
* assets/images
* sounds
* GIFs
* any JavaScript embedded inside the PHP views

Understand how the current UI is assembled before touching it.

The existing application already has:

* Gallery
* Gacha
* Battle
* Collection
* Player HUD
* Level/HP
* Fragment currency
* Daily tasks
* Multiple card rarities
* Card images
* Card stats
* Gacha animations
* Single pulls
* x10 pulls
* Collection stored in localStorage
* Theme switching
* Rarity-specific colors/effects

Preserve these existing concepts.

---

# MAIN GOAL

Make CardGameAmici feel like an actual polished **anime/gacha card RPG**, rather than a PHP website with game elements layered on top.

The target feeling should be:

**"I want to press the summon button again."**

The interface should feel:

* premium
* responsive
* game-like
* exciting
* cohesive
* readable
* satisfying
* modern

But don't simply cover everything in neon/glow effects.

Use visual effects intentionally.

---

# IMPORTANT: DO NOT GENERALIZE

Do NOT respond with:

"Here are some ways we could improve the UI."

Actually inspect the code and implement improvements.

Do NOT rewrite unrelated systems.

Do NOT replace PHP with React.

Do NOT introduce a new frontend framework.

Do NOT replace the existing styling system.

Do NOT rewrite the game architecture.

Do NOT change game probabilities.

Do NOT change card stats.

Do NOT change game mechanics.

Do NOT invent a completely different game.

Improve what is already here.

---

# PRIORITY 1 — GACHA EXPERIENCE

The gacha screen is the most important screen in this project.

Inspect `game/gacha_view.php` closely.

The current implementation has:

* a gacha intro screen
* Fragment display
* PULL x1
* PULL x10
* loading/spinner
* flash animation
* rarity-based particles
* single-card result
* multi-card results
* card collection interaction

Improve this entire flow.

## Gacha home screen

Make the summon screen immediately communicate:

* current Fragment balance
* summon cost
* x1 summon
* x10 summon
* x10 guarantee
* available rarities
* what the player can obtain

The summon buttons should feel like major game actions.

Improve:

* hierarchy
* spacing
* button design
* hover states
* pressed states
* disabled states
* loading states
* mobile sizing
* currency presentation

The player should instantly understand:

**"I have X Fragment. A summon costs Y. I can afford/not afford it."**

---

# PRIORITY 2 — MAKE SUMMONING FEEL SATISFYING

Do NOT change the actual randomness.

Improve only the presentation.

The current flow is roughly:

1. click summon
2. deduct currency
3. loading
4. flash
5. reveal
6. show card
7. collect

Make this feel much more deliberate.

Consider:

* anticipation before reveal
* rarity anticipation
* card silhouette before reveal
* controlled reveal timing
* card entrance animation
* rarity-specific effects
* subtle screen shake
* particles
* glow
* sound synchronization
* card flip
* reveal impact
* satisfying result state

However:

DO NOT make every rarity equally flashy.

The visual escalation should communicate rarity.

For example:

COMMON:
subtle

UNCOMMON:
slightly enhanced

RARE:
stronger reveal

EPIC:
dramatic

LEGENDARY:
major reveal

MYTHIC/SECRET:
special treatment

Use the existing rarity system instead of inventing another one.

---

# PRIORITY 3 — FIX THE SINGLE VS X10 EXPERIENCE

Inspect how x1 and x10 currently behave.

There is an important UX inconsistency in the existing implementation:

* single pulls produce a result that the player explicitly collects
* x10 pulls automatically add cards to the collection

Do NOT blindly change this.

Instead, determine whether this is intentional.

If it appears accidental, make the behavior consistent.

If you're unsure, flag it before making a gameplay-affecting change.

The result screen should make it extremely obvious:

* what was obtained
* rarity
* card name
* stats
* whether it is new
* whether it is a duplicate
* what happens when the player collects it

---

# PRIORITY 4 — CARD PRESENTATION

Cards are the core collectible.

Make them feel important.

Inspect the existing card rendering and image assets.

Improve:

* card proportions
* borders
* rarity treatment
* image framing
* name typography
* stats
* badges
* hover effects
* selected state
* collection state
* duplicate state

Cards should look like collectible game objects rather than normal HTML boxes.

Do not unnecessarily alter the actual card artwork.

Use the existing images.

---

# PRIORITY 5 — RARITY VISUAL SYSTEM

The repository already has these rarities:

* comune
* non-comune
* raro
* epico
* leggendario
* esotico
* mitico
* segreto

Create a coherent visual hierarchy between them.

Do NOT just change colors.

Use combinations of:

* border treatment
* glow intensity
* background
* particles
* animation
* badge design
* typography
* reveal effects

But maintain consistency across:

* gacha results
* collection
* gallery
* battle
* rarity counters

There should be ONE visual language for rarity.

---

# PRIORITY 6 — COLLECTION

Inspect the collection implementation in `index.php` and `game/collection_view.php`.

Improve the collection so it feels like a real card inventory.

It should make it easy to understand:

* owned cards
* rarity
* level
* stats
* duplicates
* collection progress

Improve:

* grid layout
* card sizing
* filtering
* sorting if already supported
* rarity filtering
* empty state
* mobile layout
* card hover/selection
* visual hierarchy

Do not add a giant complicated collection-management system unless the existing architecture supports it naturally.

---

# PRIORITY 7 — MAIN NAVIGATION

The current application uses tabs for:

* Galleria
* Gacha
* Combatti
* Collezione
* Admin

Improve the navigation while preserving those destinations.

The navigation should clearly show:

* current section
* clickable states
* hover
* active state
* mobile behavior

The current implementation places the tab navigation near the top of the screen with a fixed position.

Check carefully for:

* overlap with the HUD
* content hidden underneath navigation
* mobile viewport problems
* excessive vertical space
* z-index conflicts

Fix those issues instead of simply adding more z-index values.

---

# PRIORITY 8 — HUD

Inspect the HUD in `index.php`.

It currently contains things such as:

* level
* HP
* Fragment currency
* daily tasks
* theme toggle
* collection

Make this feel like an actual game HUD.

Do not make it huge.

It should remain readable while leaving the game content as the visual focus.

Pay special attention to mobile.

---

# PRIORITY 9 — RESPONSIVE DESIGN

This project must work properly on:

* desktop
* laptop
* tablet
* phone

Do not just shrink desktop elements.

Actually redesign the layout behavior at smaller widths.

Test:

* navigation
* HUD
* gacha buttons
* card result
* x10 results
* collection
* panels
* modals
* animations

Look specifically for:

* horizontal overflow
* buttons going off-screen
* text clipping
* cards becoming unusably small
* fixed elements overlapping
* viewport-height problems

---

# PRIORITY 10 — CURRENT CODE QUALITY PROBLEMS

While working on the UI, clean up obvious problems that directly affect reliability.

For example, inspect the current gacha implementation for brittle patterns involving:

* dynamically replacing DOM content
* dynamically appending buttons
* duplicated state
* global variables
* inline styles
* repeated rarity color definitions
* inconsistent element IDs
* event handlers
* UI state getting out of sync
* animations continuing after navigation/state changes

Do NOT perform a giant refactor.

Only improve these where it directly makes the UI more reliable.

---

# VERY IMPORTANT: PRESERVE GAME LOGIC

Do NOT modify:

* rarity probabilities
* summon costs
* card stats
* battle calculations
* XP rules
* game mechanics

unless you discover an obvious bug that prevents the UI from correctly representing the existing game state.

If you find a gameplay/logic bug, document it separately rather than silently changing it during this UI task.

---

# PERFORMANCE

Because this is a browser-based game, make the UI smoother.

Look for:

* unnecessarily repeated DOM work
* excessive particle creation
* animations that cause layout thrashing
* unnecessarily large assets
* repeated event listeners
* animations continuing when elements are hidden
* unnecessary reloads

Do not optimize prematurely.

Measure/inspect first.

Keep the game visually impressive without making low-end phones struggle.

---

# ACCESSIBILITY

Without destroying the game aesthetic:

* buttons should have usable sizes
* interactive elements should be keyboard accessible where practical
* focus states should exist
* images should have useful alt text
* text should remain readable
* important information should not rely exclusively on color

---

# IMPLEMENTATION PROCESS

Follow this exact process.

## STEP 1

Inspect the repository and identify the actual UI architecture.

## STEP 2

Inspect the current gacha screen in detail.

## STEP 3

Create a short list:

### P0

Critical UI/UX problems

### P1

High-impact improvements

### P2

Polish improvements

Do NOT give me 50 generic suggestions.

Give me the specific problems you actually found in this repository.

## STEP 4

Implement P0 and P1 improvements first.

## STEP 5

Run/test the application.

## STEP 6

Fix regressions.

## STEP 7

Implement P2 polish only after the core experience is stable.

---

# DESIGN PRINCIPLE

Every change should answer one of these questions:

**Does this make the game easier to understand?**

**Does this make collecting cards feel better?**

**Does this make summoning feel more exciting?**

**Does this make the interface faster/easier to use?**

**Does this make the game feel more polished?**

If the answer is no, don't add it.

---

# DO NOT DO THIS

Do not:

* rewrite the whole project
* convert PHP to React
* install a huge UI framework
* replace the existing game
* change probabilities
* invent mechanics
* redesign every page simultaneously
* add 100 animations
* use generic dashboard UI
* blindly apply glassmorphism everywhere
* blindly apply neon everywhere
* create unnecessary abstractions
* remove existing functionality

---

# FINAL REPORT

After implementation, report:

## Files changed

List every file.

## Gacha improvements

Explain exactly what changed.

## Card improvements

Explain exactly what changed.

## Collection improvements

Explain exactly what changed.

## Responsive improvements

Explain exactly what changed.

## Performance improvements

Explain exactly what changed.

## Bugs discovered

Separate UI bugs from gameplay/logic bugs.

## Logic NOT changed

Explicitly confirm that probabilities, costs, stats, and gameplay rules were preserved.

## Remaining work

Only list concrete things that still need attention.

Most importantly:

**Do not stop at recommendations. Make the actual improvements in the repository.**

Do the gacha screen only first. Don't touch battle, admin, or unrelated pages until I review the result.

## Working rules

- NEVER read a file in full. Use grep to find the relevant section,
  then read only that line range.
- Make targeted edits. Never rewrite an entire file.
- Only touch the files named in the current task.
- Do not change probabilities, summon costs, card stats, battle
  calculations, or XP rules. If you find a logic bug, write it down
  in a "Bugs found" section of your reply instead of fixing it.
- When done, stop and list every file you changed and what you changed.
