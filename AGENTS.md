# AGENTS.md

This file gives instructions to any AI coding assistant working on INITIAL-D.

## Project Identity

Project name: INITIAL-D

Project type: MCA Mini Project

Purpose: Race track booking website for learning PHP procedural programming with MySQL.

## Required Workflow

Do not generate the whole project at once.

Before implementing any feature:

1. Explain what the feature does.
2. Explain the implementation plan.
3. List the files that will be created or edited.
4. List the database tables that will be used.
5. Wait for user confirmation.

After implementing any feature:

1. Explain why each file exists.
2. Explain the important sections of the code.
3. Explain any new PHP or MySQL concept used.
4. Suggest one small practice modification for the student.
5. Stop and wait for user confirmation.

## Coding Standards

- Use PHP procedural style.
- Use MySQLi for database access.
- Always use prepared statements for SQL queries.
- Keep one PHP file focused on one clear responsibility.
- Separate HTML, CSS, JavaScript, and PHP where practical.
- Use beginner-friendly variable and function names.
- Avoid frameworks unless the user explicitly changes the project requirements.
- Avoid unnecessary abstractions.
- Add comments only when they explain important logic.
- Do not duplicate code when a reusable include or function is appropriate.

## Safety Rules

- Do not overwrite user work without checking existing files first.
- Do not add application code unless the user has approved the feature plan.
- Do not create database tables without explaining the schema first.
- Do not add passwords, API keys, or secrets to the repository.

## Approved Folder Structure

Use this simple structure unless the user approves a change:

```text
INITIAL-D/
├── index.php
├── README.md
├── AGENTS.md
├── NOTES.md
├── admin/
├── user/
├── vendor/
├── config/
├── includes/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── uploads/
│   └── tracks/
├── database/
└── docs/
```

Do not create feature-specific PHP files until the user approves that feature plan.

## Current Project State

The project currently contains only a simple folder scaffold, root placeholder files, and learning documentation. Application code should be added feature by feature.
