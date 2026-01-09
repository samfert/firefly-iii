[![Packagist][packagist-shield]][packagist-url]
[![License][license-shield]][license-url]
[![Stargazers][stars-shield]][stars-url]
[![Donate][donate-shield]][donate-url]

<!-- PROJECT LOGO -->
<br />
<p align="center">
  <a href="https://firefly-iii.org/">
    <img src="https://raw.githubusercontent.com/firefly-iii/firefly-iii/develop/.github/assets/img/logo-small.png" alt="Firefly III" width="120" height="178">
  </a>
</p>
  <h1 align="center">Firefly III</h1>

  <p align="center">
    A free and open source personal finance manager
    <br />
    <a href="https://docs.firefly-iii.org/"><strong>Explore the documentation</strong></a>
    <br />
    <br />
    <a href="https://demo.firefly-iii.org/">View the demo</a>
    ·
    <a href="https://github.com/firefly-iii/firefly-iii/issues">Report a bug</a>
    ·
    <a href="https://github.com/firefly-iii/firefly-iii/issues">Request a feature</a>
    ·
    <a href="https://github.com/firefly-iii/firefly-iii/discussions">Ask questions</a>
  </p>

<!-- MarkdownTOC autolink="true" -->

- [About Firefly III](#about-firefly-iii)
  - [Purpose](#purpose)
- [Features](#features)
- [Who's it for?](#whos-it-for)
- [The Firefly III eco-system](#the-firefly-iii-eco-system)
- [Getting Started](#getting-started)
- [Contributing](#contributing)
- [Support the development of Firefly III](#support-the-development-of-firefly-iii)
- [License](#license)
- [Do you need help, or do you want to get in touch?](#do-you-need-help-or-do-you-want-to-get-in-touch)
- [Acknowledgements](#acknowledgements)

- [Technical Architecture](#technical-architecture)
- [Development Setup](#development-setup)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [Testing](#testing)
- [Configuration](#configuration)

<!-- /MarkdownTOC -->

## About Firefly III

<p align="center">
	<img src="https://raw.githubusercontent.com/firefly-iii/firefly-iii/develop/.github/assets/img/imac-complete.png" alt="Firefly III on iMac" />
</p>

"Firefly III" is a (self-hosted) manager for your personal finances. It can help you keep track of your expenses and income, so you can spend less and save more. Firefly III supports the use of budgets, categories and tags. Using a bunch of external tools, you can import data. It also has many neat financial reports available.

Firefly III should give you **insight** into and **control** over your finances. Money should be useful, not scary. You should be able to *see* where it is going, to *feel* your expenses and to... wow, I'm going overboard with this aren't I?

But you get the idea: this is your money. These are your expenses. Stop them from controlling you. I built this tool because I started to dislike money. Having money, not having money, paying bills with money, you get the idea. But no more. I want to feel "safe", whatever my balance is. And I hope this tool can help you. I know it helps me.

### Purpose

<p align="center">
  <img src="https://raw.githubusercontent.com/firefly-iii/firefly-iii/develop/.github/assets/img/ipad-complete.png" alt="Firefly III on iPad" width="600">
</p>

Personal financial management is pretty difficult, and everybody has their own approach to it. Some people make budgets, other people limit their cashflow by throwing away their credit cards, others try to increase their current cashflow. There are tons of ways to save and earn money. Firefly III works on the principle that if you know where your money is going, you can stop it from going there.

By keeping track of your expenses and your income you can budget accordingly and save money. Stop living from paycheck to paycheck but give yourself the financial wiggle room you need.

You can read more about the purpose of Firefly III in the [documentation](https://docs.firefly-iii.org/).

## Features

Firefly III is pretty feature packed. Some important stuff first:

* It is completely self-hosted and isolated, and will never contact external servers until you explicitly tell it to.
* It features a REST JSON API that covers almost every part of Firefly III.

The most exciting features are:

* Create [recurring transactions to manage your money](https://docs.firefly-iii.org/explanation/financial-concepts/recurring/).
* [Rule based transaction handling](https://docs.firefly-iii.org/how-to/firefly-iii/features/rules/) with the ability to create your own rules.

Then the things that make you go "yeah OK, makes sense".

* A [double-entry](https://en.wikipedia.org/wiki/Double-entry_bookkeeping_system) bookkeeping system.
* Save towards a goal using [piggy banks](https://docs.firefly-iii.org/explanation/financial-concepts/piggy-banks/).
* View [income and expense reports](https://docs.firefly-iii.org/how-to/firefly-iii/finances/reports/).

And the things you would hope for but not expect:

* 2 factor authentication for extra security 🔒.
* Supports [any currency you want](https://docs.firefly-iii.org/how-to/firefly-iii/features/currencies/).
* There is a [Docker image](https://docs.firefly-iii.org/how-to/firefly-iii/installation/docker/).

And to organise everything:

* Clear views that should show you how you're doing.
* Easy navigation through your records.
* Lots of charts because we all love them.

Many more features are listed in the [documentation](https://docs.firefly-iii.org/explanation/firefly-iii/about/introduction/).

## Who's it for?
<img src="https://raw.githubusercontent.com/firefly-iii/firefly-iii/develop/.github/assets/img/iphone-complete.png" alt="Firefly III on iPhone" align="left" width="250">

 This application is for people who want to track their finances, keep an eye on their money **without having to upload their financial records to the cloud**. You're a bit tech-savvy, you like open source software and you don't mind tinkering with (self-hosted) servers.
 
 <br clear="left"/>

## The Firefly III eco-system

Several users have built pretty awesome stuff around the Firefly III API. [Check out these tools in the documentation](https://docs.firefly-iii.org/references/firefly-iii/third-parties/apps/).

## Getting Started

There are many ways to run Firefly III
1. There is a [demo site](https://demo.firefly-iii.org) with an example financial administration already present.
2. You can [install it on your server](https://docs.firefly-iii.org/how-to/firefly-iii/installation/self-managed/).
3. You can [run it using Docker](https://docs.firefly-iii.org/how-to/firefly-iii/installation/docker/).
4. You can [deploy via Kubernetes](https://firefly-iii.github.io/kubernetes/).
5. You can [install it using Softaculous](https://www.softaculous.com/softaculous/apps/others/Firefly_III).
6. You can [install it using AMPPS](https://www.ampps.com/).
7. You can [install it on Cloudron](https://cloudron.io/store/org.fireflyiii.cloudronapp.html).
8. You can [install it on Lando](https://gist.github.com/ArtisKrumins/ccb24f31d6d4872b57e7c9343a9d1bf0).
9. You can [install it on Yunohost](https://github.com/YunoHost-Apps/firefly-iii).

## Contributing

You can contact me at [james@firefly-iii.org](mailto:james@firefly-iii.org), you may open an issue in the [main repository](https://github.com/firefly-iii/firefly-iii) or contact me through [gitter](https://gitter.im/firefly-iii/firefly-iii) and [Mastodon](https://fosstodon.org/@ff3).

Of course, there are some [contributing guidelines](https://docs.firefly-iii.org/explanation/support/#contributing-code) and a [code of conduct](https://github.com/firefly-iii/firefly-iii/blob/main/.github/code_of_conduct.md), which I invite you to check out.

I can always use your help [squashing bugs](https://docs.firefly-iii.org/explanation/support/), thinking about [new features](https://docs.firefly-iii.org/explanation/support/) or [translating Firefly III](https://docs.firefly-iii.org/how-to/firefly-iii/development/translations/) into other languages.

[Sonarcloud][sc-project-url] scans the code of Firefly III. If you want to help improve Firefly III, check out the latest reports and take your pick!

[![Quality Gate Status][sc-gate-shield]][sc-project-url] [![Bugs][sc-bugs-shield]][sc-project-url] [![Code Smells][sc-smells-shield]][sc-project-url] [![Vulnerabilities][sc-vuln-shield]][sc-project-url]

There is also a [security policy](https://github.com/firefly-iii/firefly-iii/security/policy).

[![CII Best Practices][bp-badge]][bp-url]

<!-- SPONSOR TEXT -->

## Support the development of Firefly III

If you like Firefly III and if it helps you save lots of money, why not send me a dime for every dollar saved! 🥳

OK that was a joke. If you feel Firefly III made your life better, please consider contributing as a sponsor. Please check out my [Patreon](https://www.patreon.com/jc5) and [GitHub Sponsors](https://github.com/sponsors/JC5) page for more information. You can also [buy me a ☕️ coffee at ko-fi.com](https://ko-fi.com/Q5Q5R4SH1). Thank you for your consideration.

<!-- END OF SPONSOR TEXT -->

## License

This work [is licensed](https://github.com/firefly-iii/firefly-iii/blob/main/LICENSE) under the [GNU Affero General Public License v3](https://www.gnu.org/licenses/agpl-3.0.html).

<!-- HELP TEXT -->

## Do you need help, or do you want to get in touch?

Do you want to contact me? You can email me at [james@firefly-iii.org](mailto:james@firefly-iii.org) or get in touch through one of the following support channels:

- [GitHub Discussions](https://github.com/firefly-iii/firefly-iii/discussions/) for questions and support
- [Gitter.im](https://gitter.im/firefly-iii/firefly-iii) for a good chat and a quick answer
- [GitHub Issues](https://github.com/firefly-iii/firefly-iii/issues) for bugs and issues
- <a rel="me" href="https://fosstodon.org/@ff3">Mastodon</a> for news and updates

<!-- END OF HELP TEXT -->


## Acknowledgements

Over time, [many people have contributed to Firefly III](https://github.com/firefly-iii/firefly-iii/graphs/contributors). I'm grateful for their support and code contributions.

The Firefly III logo is made by the excellent Cherie Woo.

## Technical Architecture

Firefly III is built on the Laravel PHP framework (version 12) and follows a clean, modular architecture based on established design patterns.

### Technology Stack

The application uses PHP 8.4+ with Laravel 12 as the core framework, supporting both MySQL and PostgreSQL databases. The frontend combines Twig templating with modern JavaScript, while Redis handles caching and session management. Authentication is managed through Laravel Passport for API tokens and Laravel's built-in authentication for web sessions.

### Core Design Patterns

The codebase implements the Repository Pattern to abstract data access, separating business logic from database operations. This is complemented by a Service Layer that encapsulates complex business logic, and an Event-Driven Architecture using Laravel's event system for decoupled components. The application follows Double-Entry Bookkeeping principles where every transaction creates balanced debit and credit entries.

### Key Architectural Components

Models represent the core data entities (Account, Transaction, Budget, etc.) with Eloquent ORM relationships. Repositories provide data access abstraction through interfaces like AccountRepositoryInterface. Services contain business logic for operations like transaction creation and rule processing. Controllers handle HTTP requests and delegate to services, while Transformers format data for API responses using the Fractal library.

## Development Setup

### Prerequisites

Before setting up the development environment, ensure you have PHP 8.4 or higher with required extensions (bcmath, curl, fileinfo, intl, json, mbstring, openssl, pdo, session, xml), Composer for PHP dependency management, Node.js and npm for frontend assets, and either MySQL 8.0+ or PostgreSQL 12+.

### Local Installation

Clone the repository and install dependencies by running `composer install` followed by `npm install`. Copy the environment file with `cp .env.example .env` and generate an application key using `php artisan key:generate`. Configure your database connection in the .env file, then run migrations with `php artisan migrate --seed`. Finally, start the development server with `php artisan serve`.

### Environment Configuration

Key environment variables include APP_KEY (auto-generated application encryption key), DB_CONNECTION (database driver: mysql or pgsql), DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, and DB_PASSWORD for database configuration. MAIL_MAILER configures the email driver, and APP_URL sets the application URL for links.

## Project Structure

The application follows Laravel's standard directory structure with some Firefly III-specific additions.

### Directory Overview

The `app/` directory contains the core application code including Models (Eloquent models for all entities), Http/Controllers (web and API controllers), Repositories (data access layer implementations), Services (business logic services), Events and Listeners (event-driven components), Jobs (queued background tasks), and Transformers (API response formatters).

The `config/` directory holds configuration files, with firefly.php containing application-specific settings. The `database/` directory includes migrations (database schema definitions) and seeders (initial data population). The `routes/` directory defines web.php for web routes, api.php for API endpoints, and console.php for Artisan commands. The `resources/` directory contains views (Twig templates), lang (translation files), and assets (CSS/JS source files). Finally, `tests/` contains Unit and Feature test suites.

### Key Model Relationships

Account is the central entity linked to transactions, with types including Asset, Expense, Revenue, and Liability. TransactionJournal groups related Transaction records in the double-entry system. Budget and Category provide transaction classification, while Bill tracks recurring expected expenses. Rule defines automation triggers and actions, and PiggyBank manages savings goals linked to accounts.

## API Documentation

Firefly III provides a comprehensive REST API for programmatic access to all features.

### Authentication

The API uses OAuth2 authentication via Laravel Passport. Generate a Personal Access Token through the web interface under Profile > OAuth, then include the token in requests using the Authorization header with Bearer token format.

### API Versioning

The current API version is 2.1.0. All endpoints are prefixed with `/api/v1/` for consistency.

### Common Endpoints

Account management is available at GET/POST `/api/v1/accounts` for listing and creating accounts, and GET/PUT/DELETE `/api/v1/accounts/{id}` for individual account operations. Transaction management uses GET/POST `/api/v1/transactions` and GET/PUT/DELETE `/api/v1/transactions/{id}`. Budget operations are at `/api/v1/budgets` endpoints, and category management at `/api/v1/categories`.

### Response Format

All API responses use JSON format with consistent structure. Successful responses include a "data" key with the requested resource(s), while error responses include "message" and "errors" keys with details.

## Database Schema

The database uses a normalized relational schema optimized for financial data integrity.

### Core Tables

The `users` table stores user accounts and authentication data. The `accounts` table contains financial accounts with foreign keys to account_types and users. The `transactions` table holds individual transaction legs with amounts and account references. The `transaction_journals` table groups transactions into complete financial events. The `budgets` and `categories` tables provide classification systems, while `bills` tracks recurring expected expenses. The `rules`, `rule_triggers`, and `rule_actions` tables define automation logic.

### Key Relationships

Users own all financial data through user_id foreign keys. Accounts belong to AccountTypes which define their behavior. TransactionJournals contain multiple Transactions (minimum 2 for double-entry). Budgets and Categories link to transactions through pivot tables, and Bills link to TransactionJournals for recurring expense tracking.

## Testing

The project includes comprehensive test suites for ensuring code quality.

### Running Tests

Execute the full test suite with `php artisan test` or `./vendor/bin/phpunit`. Run only unit tests using `composer unit-test` and integration tests with `composer integration-test`. Generate a coverage report using `composer coverage`.

### Test Structure

Unit tests in `tests/Unit/` test individual classes in isolation. Feature tests in `tests/Feature/` test complete features and API endpoints. The project uses PHPUnit 12 as the testing framework with Laravel's testing utilities.

### Code Quality Tools

PHPStan performs static analysis (run with `./vendor/bin/phpstan analyse`). Laravel Pint handles code style checking, and SonarQube integration is available for comprehensive code quality analysis.

## Configuration

Firefly III offers extensive configuration options through environment variables and the config files.

### Feature Flags

Located in `config/firefly.php`, feature flags control optional functionality. The `webhooks` flag enables webhook support for external integrations. The `export` flag enables data export functionality. The `handle_debts` flag enables liability/debt management features.

### Security Settings

AUTHENTICATION_GUARD configures the authentication method (default: web). DISABLE_FRAME_HEADER controls X-Frame-Options header. DISABLE_CSP_HEADER controls Content-Security-Policy header. ALLOW_WEBHOOKS enables/disables webhook functionality.

### Localization

The application supports 30+ languages configured in `config/firefly.php`. DEFAULT_LANGUAGE sets the default language (e.g., en_US). DEFAULT_LOCALE configures number and date formatting. Users can override these in their preferences.

[packagist-shield]:https://img.shields.io/packagist/v/grumpydictator/firefly-iii.svg?style=flat-square
[packagist-url]: https://packagist.org/packages/grumpydictator/firefly-iii
[license-shield]: https://img.shields.io/github/license/firefly-iii/firefly-iii.svg?style=flat-square
[license-url]: https://www.gnu.org/licenses/agpl-3.0.html
[stars-shield]: https://img.shields.io/github/stars/firefly-iii/firefly-iii.svg?style=flat-square
[stars-url]: https://github.com/firefly-iii/firefly-iii/stargazers
[donate-shield]: https://img.shields.io/badge/donate-%24%20%E2%82%AC-brightgreen?style=flat-square
[donate-url]: #support-the-development-of-firefly-iii
[build-shield]: https://api.travis-ci.com/firefly-iii/firefly-iii.svg?branch=master
[build-url]: https://travis-ci.com/github/firefly-iii/firefly-iii
[sc-gate-shield]: https://sonarcloud.io/api/project_badges/measure?project=firefly-iii_firefly-iii&metric=alert_status
[sc-bugs-shield]: https://sonarcloud.io/api/project_badges/measure?project=firefly-iii_firefly-iii&metric=bugs
[sc-smells-shield]: https://sonarcloud.io/api/project_badges/measure?project=firefly-iii_firefly-iii&metric=code_smells
[sc-vuln-shield]: https://sonarcloud.io/api/project_badges/measure?project=firefly-iii_firefly-iii&metric=vulnerabilities
[sc-project-url]: https://sonarcloud.io/dashboard?id=firefly-iii_firefly-iii
[bp-badge]: https://bestpractices.coreinfrastructure.org/projects/6335/badge
[bp-url]: https://bestpractices.coreinfrastructure.org/projects/6335    
