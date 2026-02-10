---
description: Implement a single chunk from the OAuth Login plan
---

# Implement Chunk Workflow

Use this workflow to implement one chunk at a time from the OAuth extension plan.

## Steps

1. **Read progress file** to determine the current chunk
   - Check `.windsurf/progress.md` for current phase and chunk
   - If no progress file exists, start with Phase 3, Chunk 3.1

2. **Write/update unit tests FIRST** (TDD-lite)
   - Create test file if it doesn't exist
   - Add test methods for the chunk's functionality
   - Tests should initially fail (red)

3. **Implement the chunk** (1-3 files max)
   - Follow the plan specifications
   - Keep changes minimal and focused
   - Follow workspace rules in `.windsurf/rules/`

4. **Run validation**
   ```bash
   // turbo
   composer qa
   ```

5. **If FAIL** → fix and re-run step 4
   - Address PHPCS errors first
   - Then fix failing tests
   - Do not proceed until green

6. **Update progress file** with results
   - Mark chunk as completed
   - Record test counts and status
   - Note any blockers

7. **Pick next chunk or stop at gate**
   - If at a gate (end of phase), run full gate validation
   - Otherwise, proceed to next chunk
