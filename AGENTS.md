# SupportDesk Lite — Codex Instructions

## Project Goal

Build a small, portfolio-ready help-desk ticket application with PHP and Laravel. The project must be complete enough to demonstrate practical Laravel knowledge in a job application, while remaining small enough to finish in one day.

The developer is experienced with React, Next.js, TypeScript, Node.js, Express, APIs, authentication, testing, and SQL databases, but is new to modern PHP and Laravel. Treat this as both a real project and a guided learning exercise.

## Working Rule

Work on exactly one small phase at a time.

For every task:

1. Inspect the current project state.
2. State a short plan.
3. Explain the Laravel or PHP concept being introduced in plain language.
4. Implement only the requested phase.
5. Run the smallest relevant verification commands.
6. Fix failures caused by the work.
7. Summarize changed files, commands run, results, and the next logical phase.
8. Stop. Do not begin the next phase until requested.

Do not attempt to build the entire application in one pass.

## Planned Stack

- Current stable Laravel version
- Supported modern PHP version
- Composer
- Blade templates
- Tailwind CSS
- SQLite for local development
- Laravel's official authentication tooling
- PHPUnit or Pest, following the selected Laravel starter setup
- Git and GitHub

Prefer Laravel-native features and official packages. Do not add dependencies unless the current phase genuinely requires them.

## Intended Application Scope

The finished application will include:

- User registration, login, and logout
- A `Ticket` model belonging to a user
- Ticket title, description, priority, and status
- Create, list, view, edit, and delete operations
- Validation with Form Request classes
- Authorization so users manage only their own tickets
- Status filtering
- A small authenticated dashboard
- Focused feature tests
- A professional README and truthful CV project entry

Features are introduced only when their phase is requested.

## Scope Boundaries

Do not add administrator roles, email notifications, file uploads, payments, external APIs, real-time features, Docker, Redis, queues, repository layers, or other speculative features unless explicitly requested later.

Do not refactor unrelated code or redesign completed features without a concrete reason.

## Code Standards

- Follow Laravel conventions before inventing abstractions.
- Use migrations and Eloquent relationships for data modelling.
- Use resourceful routes and route model binding where appropriate.
- Keep controllers focused.
- Use Form Requests for non-trivial validation.
- Use policies for ownership authorization.
- Use named routes and reusable Blade components where helpful.
- Add type declarations where appropriate without fighting framework conventions.
- Keep the interface responsive and accessible.
- Never expose secrets or commit `.env`, dependencies, generated build files, or local database files.

## Teaching Expectations

Keep explanations concise and tied to the code being changed. When introducing a Laravel concept, compare it to Node.js, Express, React, Prisma, or another familiar tool when that makes the concept easier to understand.

Do not hide important steps behind automation. Show the commands used and explain what the important generated files do. Avoid long tutorials unrelated to the current phase.

## Testing and Verification

Run only checks relevant to the current phase, such as:

```bash
php artisan migrate
php artisan test
npm run build
```

Do not claim success when a required command fails. Clearly distinguish errors caused by the project from missing local tooling or environment problems.

## Git Safety

- Inspect `git status` before making changes.
- Preserve existing user changes.
- Never discard or overwrite unrelated work.
- Do not create a remote repository, push commits, deploy, or publish anything without explicit permission.
- Keep commits small and tied to completed phases when commits are requested.

## Required Task Summary

End every implementation phase with:

```text
PHASE=
CHANGED_FILES=
CONCEPTS_LEARNED=
COMMANDS_RUN=
VERIFICATION=
BLOCKERS=
NEXT_RECOMMENDED_PHASE=
```
