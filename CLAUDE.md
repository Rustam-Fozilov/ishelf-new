# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

# iShelf Application Architecture Documentation

## Project Overview

**iShelf** is a comprehensive shelf management and product inventory system built with Laravel 12. It's designed for retail operations to manage products on physical shelves, auto-order inventory, track pricing (price tags/senniks), and manage stock across multiple branches.

The application serves as a backend API for a multi-branch retail operation with complex inventory management, price tag management, and shelf organization features.

---

## Business Domain & Core Concepts

### Primary Business Functions

1. **Shelf Management** - Manage physical shelves in retail branches
   - Organize products by floor, placement, and size
   - Track shelf changes and updates
   - Support different product types (Phone, TV, Refrigerator, Laptop, etc.)

2. **Product & Stock Management**
   - Manage products with categories, brands, attributes, and parameters
   - Track stock levels across branches via MongoDB
   - Sync product data from external "Idea" system
   - Monitor product lifecycle (active/sold status)

3. **Price Tag Management (Senniks)**
   - Create and manage "senniks" (price tag configurations)
   - Track pricing months and bonus structures
   - Print price tags with templates
   - Analytic reporting by branch and category

4. **Auto-Ordering System**
   - Automatically fill shelves with products based on configurable rules
   - Two versions: Basic (V1) and Advanced (V2)
   - Support sorting by various product attributes
   - Manage stock priorities for shelves

5. **Application/Request Workflow**
   - Handle shelf application requests with multi-step approval
   - Track application history and status changes
   - Link to uploads and documentation

6. **Telegram Bot Integration**
   - Notify regional and branch directors about shelf updates
   - Real-time notifications via Telegraph service

---

## Key Architectural Patterns

### 1. Service Layer Architecture
- **Location**: `app/Services/`
- **Pattern**: Business logic isolated in service classes
- **Examples**:
  - `ShelfTempService` - Manages temporary shelf product arrangements
  - `PriceTagService` - Handles price tag creation and analytics
  - `ProductService` - Product synchronization and management
  - `StockByBranchService` - Branch-specific stock operations

### 2. Category-Specific Product Services
- **Pattern**: Strategy pattern for different product types
- **Location**: `app/Services/ProductShelf/`
- **Implementation**:
  ```
  TvService, PhoneService, LaptopService, RefrigeratorService,
  WashingService, MicrowavesService, GasCookersService, etc.
  ```
- **Interface**: `app/Interfaces/ProductShelfInterface`
  - Each service implements: `createTemp()`, `tempAddProduct()`, `deleteTempProduct()`, `tempAutoOrderProduct()`

### 3. Dual-Database Architecture
- **MySQL**: Primary relational data (products, shelves, users, categories, price tags)
- **MongoDB**: Stock data via `Stock` model with `mongodb` connection
  - Enables dynamic per-branch stock tables (using `setTable($branch->token)`)
  - StockByBranch model uses dynamic table names

### 4. Queue-Based Processing
- **Queue Driver**: Redis (QUEUE_CONNECTION=redis)
- **Dashboard**: Laravel Horizon for monitoring
- **Job Patterns**:
  - Async jobs for stock syncs, price tag operations, product checks
  - Telegram notifications via queue jobs
  - Product and price tag batch operations

### 5. Auditing & Change Tracking
- **Package**: owen-it/laravel-auditing
- **Models with Auditing**: `Shelf`, `ProductShelf`
- **Tracks**: Created, updated, deleted, restored events
- **Use Case**: Compliance and change history for shelf modifications

### 6. Temporary Data Pattern
- **Models**: `ProductShelfTemp`, `SennikTemp`, `PriceTagGoodTemp`, etc.
- **Purpose**: Stage changes before finalizing (draft/temp workflow)
- **Example**: Users arrange products temporarily, then save/commit

---

## External Integrations

### 1. Anketa Service (Branch Sync)
- **Integration Type**: REST API via Saloon PHP
- **Purpose**: Synchronize branch information
- **Connector**: `app/Http/Integrations/Anketa/AnketaConnector.php`
- **Auth**: Bearer token
- **Config**: `ANKETA_BASE_URL`, `ANKETA_TOKEN` in `.env`
- **Request**: `BranchSyncRequest`

### 2. Invoice Service (User Validation)
- **Integration Type**: REST API via Saloon PHP
- **Purpose**: Validate users by PINFL (national ID)
- **Connector**: `app/Http/Integrations/Invoice/InvoiceConnector.php`
- **Auth**: Bearer token
- **Config**: `INVOICE_BASE_URL`, `INVOICE_TOKEN` in `.env`
- **Request**: `GetUserByPinflRequest`

### 3. Idea API (Product Data)
- **Integration Type**: REST API via Saloon PHP
- **Purpose**: Product catalog, attributes, pricing
- **Connector**: `app/Http/Integrations/Idea/IdeaConnector.php`
- **Base URL**: Hardcoded to `https://api.idea.uz/api/`
- **Requests**: 
  - `ProductAttributeRequest` - Fetch product attributes
  - `SearchProductBySku` - Search product by SKU

### 4. Telegram Bot (Telegraph)
- **Package**: defstudio/telegraph
- **Purpose**: Real-time notifications to users
- **Service**: `TelegraphService` manages bot interactions
- **Config**: `config/telegraph.php`, `TELEGRAM_TOKEN` in `.env`
- **Handler**: `StartHandler` for webhook handling

---

## Database Architecture

### MongoDB Usage
```
Connection: 'mongodb' in config/database.php
Models:
  - Stock (default)
  - StockByBranch (uses dynamic table names by branch token)

Purpose: Handle high-volume stock data across branches
Benefit: Flexible schema, easy per-branch data isolation
```

### Key Tables (MySQL)

#### Product Domain
- `products` - Master product data
- `product_categories` - Categories (TV, Refrigerator, etc.)
- `product_attributes` - Product specs (size, weight, RAM, etc.)
- `product_parameters` - User-defined product parameters
- `product_prices` - Historical price tracking
- `product_months` - Monthly pricing bonuses
- `category_brands` - Brands associated with categories

#### Shelf Domain
- `shelves` - Physical shelf definitions
- `product_shelf` - Products on shelves (auditable)
- `product_shelf_temp` - Temporary product arrangements (staging)
- `shelf_changes` - Change history (who modified, when)
- `shelf_stock_priorities` - Priority products for auto-ordering
- `auto_orderings` - Auto-order configurations per shelf
- `phone_shelves` - Special configuration for phone shelves
- `phone_shelf_items` - Items in phone shelf displays

#### Price Tag Domain
- `price_tag_goods` - Products included in price tags
- `price_tag_senniks` - Price tag configurations (senniks)
- `price_tag_sennik_temp` - Temporary senniks (staging)
- `price_tag_templates` - Price tag print templates
- `price_tag_months` - Monthly pricing in senniks
- `price_tag_prints` - Print history tracking
- `price_tag_logs` - Change logs for price tags

#### Branch & Organization
- `branches` - Store/branch locations
- `regions` - Geographic regions
- `user_branches` - User-to-branch assignments
- `stock_by_branches` - Dynamic per-branch stock data

#### User & Access Control
- `users` - User accounts
- `roles` - User roles
- `permissions` - Fine-grained permissions
- `role_perms` - Role-to-permission mappings
- `user_categories` - User-specific category access

#### Workflow
- `applications` - Shelf application requests
- `application_branches` - Application-to-branch assignments
- `app_histories` - Application state history
- `print_logs` - Print job tracking

---

## Queue & Job Processing

### Job Structure
Located in `app/Jobs/`

#### Product Jobs
- `ProductSyncJob` - Sync product data (queue: product_log)
- `ProductCheckJob` - Validate individual products
- `ProductPriceMonthsJob` - Process monthly pricing
- `ProductPriceMonthUpdateJob` - Update monthly prices
- `SyncAttributesJob` - Sync product attributes

#### PriceTag Jobs
- `PriceTagSyncJob` - Sync price tag changes
- `PriceTagItemJob` - Process individual price tag items
- `MoveSennikJob` - Move senniks between states
- `NotifySennikJob` - Notify about sennik changes
- `ProcessPriceTagPrintJob` - Handle printing

#### Stock & Notification Jobs
- `SendStockToBotJob` - Notify bot about stock changes
- `SendNewStockToBotJob` - Alert new stock
- `NotifyShelfUpdatedJob` - Telegram notification when shelf updates
- `UploadAttributesJob` - Batch upload product attributes

### Queue Configuration
- **Driver**: Redis
- **Horizon Dashboard**: Available at `/horizon`
- **Failed Jobs**: Stored for inspection
- **Named Queues**: `product_log`, `default`

---

## Unique/Non-Standard Implementations

### 1. Dynamic Stock Table Names
```php
// Stock by branch uses branch token as table name
(new StockByBranch())
    ->setTable($branch->token)
    ->newQuery()
    ->where('sku', $sku)
```
- Enables isolated stock per branch
- Dynamic schema approach

### 2. Temporary State Pattern (Shelf Ordering)
- **Flow**: Load → Temp Stage → Arrange → Commit
- **Models**: `ProductShelfTemp`, `SennikTemp`, `PriceTagGoodTemp`
- **Purpose**: Draft-save-publish pattern for complex operations

### 3. Auto-Ordering V2
- **Endpoint**: `POST /ordering/temp/auto/v2`
- **Feature**: Advanced product arrangement algorithm
- **Configuration**: Saved in `AutoOrdering` model with JSON sorting rules
- **Previous Version**: Basic V1 still supported

### 4. Category-Based Service Mapping
```php
// ShelfTempService.setService() maps category_sku to specific service
case 117: // TV
case 934: // Phone
case 438: // Laptop
// etc - 15+ categories with custom logic
```

### 5. Price Tag Template System
- Supports multiple print templates per category
- Template can be attached to sennik for consistency
- Print tracking with PriceTagPrints model

### 6. Phone Shelf Special Handling
- `PhoneShelf` model for display management
- `PhoneShelfItem` for individual display items
- Start point configuration for positioning
- Image upload support with physical coordinates

### 7. Multi-branch Permissions
- Users assigned to specific branches via `user_branches`
- Users assigned to specific categories via `user_categories`
- Role-based permission system with granular control

### 8. Application Workflow Steps
- Multi-step approval process for shelf applications
- Step tracking: 1-N steps with status management
- History tracking with `AppHistory` model

---

## Important Business Logic Patterns

### Auto-Ordering (Core Feature)
**Location**: `ShelfTempService::autoOrderingV2()`

```
Process:
1. Get available stock for shelf from branch
2. Apply sorting rules from AutoOrdering config
3. Fill ProductShelfTemp slots with sorted products
4. Respect stock availability and priority products
5. Save temporary arrangement
```

**Key Considerations**:
- Stock availability from `StockByBranch` (MongoDB)
- Category match validation
- Prevent duplicate SKUs beyond available stock
- Respect shelf physical constraints (size, floor)

### Stock Sync Workflow
**Location**: `ProductService::syncStock()`, `ProductCheckJob`

```
Trigger: External product log received
Process:
1. ProductLog created with product batch
2. ProductSyncJob dispatched
3. ProductCheckJob processes each product
4. For each product:
   - Create/update category
   - Create/update product record
   - Update stock in branch-specific table
   - Mark old products as sold if not in new log
   - Reset sold status for re-stocked items
5. Notify bot about new/removed stock
```

### Price Tag (Sennik) Management
**Location**: `PriceTagService`

```
Workflow:
1. Create SennikTemp with products (staging)
2. Attach template for print format
3. Assign to branches (many-to-many)
4. Change step (draft → ready → printed, etc.)
5. Track prints in PriceTagPrints
6. Generate analytics by branch/category
7. Group results (by category, print type, printed/unprinted)
```

### Shelf Update Notifications
**Location**: `NotifyShelfUpdatedJob`

```
Trigger: Shelf modified
Notification Flow:
1. Dispatch NotifyShelfUpdatedJob(shelf)
2. Fetch regional directors for branch region
3. Fetch branch directors
4. Send Telegram notifications via Telegraph
5. Message includes shelf and branch details
```

---

## API Route Structure

### Authentication
- **Provider**: Sanctum (Laravel Sanctum)
- **Login Endpoint**: `POST /auth/login`
- **Token Required**: All routes except login use `auth:sanctum` middleware
- **Custom Middleware**: `projects_token` for service-to-service auth

### API Groups
- `/user` - User management
- `/shelf` - Shelf CRUD and management
- `/priority` - Stock priority configuration
- `/admin` - Admin sync operations (branch sync, attribute sync)
- `/branch` - Branch listing and status
- `/category` - Product category management
- `/stock` - Stock synchronization
- `/product` - Product listing, updates, parameters
- `/upload` - File uploads (images, Excel, MML)
- `/print` - Print log management
- `/ordering/temp` - Temporary shelf arrangements and auto-ordering
- `/ordering/product` - Save and list ordered products
- `/phone` - Phone shelf specific operations
- `/price_tag` - Price tag management (list, print, senniks, templates, analytics)
- `/applications` - Shelf application workflow
- `/v2` - Version 2 endpoints (product lists)

---

## Helper Functions & Utilities

**Location**: `app/helper.php`

Key utilities:
- `success($data)` - JSON success response
- `throwError($message)` - Throw validation error with message
- `throwErrors($errors, $code)` - Throw multiple errors
- `checkPinflNumber($pinfl)` - Validate Uzbekistan national ID
- `translit($text, $lang)` - Russian/Uzbek Cyrillic to Latin conversion
- `validateData($data, $rules)` - Validation wrapper
- `phoneClear($phone)` - Normalize phone numbers

Language: Uzbek, Russian support with transliteration

---

## Data Import System

**Location**: `app/Imports/`

### Excel Import System
- Uses `maatwebsite/excel` for processing
- Implements `ToCollection`, `WithChunkReading` for batch processing
- Category-specific imports:
  - `TvImport`, `MobileImport`, `LaptopImport`
  - `RefrigeratorImport` (Fridge), `FreezerImport`
  - `MicrowaveImport`, `GasImport`, `WashingImport`
  - `ParameterImport` - Product parameters

### MML Import
- `MMLImport` - Proprietary MML format handling

---

## Configuration Files

### Key Config Files
- `config/database.php` - MySQL & MongoDB connections
- `config/queue.php` - Redis queue configuration
- `config/horizon.php` - Queue monitoring dashboard
- `config/audit.php` - Change tracking configuration
- `config/telegraph.php` - Telegram bot settings
- `config/services.php` - Third-party service credentials

---

## Development Setup

### Essential Commands

#### Development Server
```bash
# Full development stack (server + queue + logs + vite)
composer dev
# This runs concurrently:
# - php artisan serve (server on port 8000)
# - php artisan queue:listen --tries=1 (queue worker)
# - php artisan pail --timeout=0 (live logs)
# - npm run dev (Vite dev server)

# Individual commands
php artisan serve                    # Start Laravel development server
npm run dev                          # Start Vite dev server
npm run build                        # Build assets for production
```

#### Testing
```bash
composer test                        # Run PHPUnit tests (clears config first)
php artisan test                     # Run tests directly
php artisan test --filter=TestName   # Run specific test
```

#### Queue Management
```bash
php artisan queue:listen --tries=1   # Run queue worker
php artisan horizon                  # Start Horizon dashboard
php artisan queue:failed             # List failed jobs
php artisan queue:retry {id}         # Retry failed job
```

#### Artisan Commands
```bash
php artisan app:auto-ordering-command              # Run auto-ordering process
php artisan app:branch-sync-command                # Sync branches from Anketa
php artisan app:delete-inactive-uploads-command    # Clean up inactive uploads
php artisan app:check-sennik-active-command        # Check sennik status
php artisan app:service-command                    # Service utility command
php artisan app:back-up-command                    # Create backup
php artisan app:back-up-move-command               # Move backup files
```

#### Database
```bash
php artisan migrate                  # Run migrations
php artisan migrate:fresh            # Drop all tables and re-migrate
php artisan migrate:rollback         # Rollback last migration
php artisan db:seed                  # Run database seeders
```

#### Code Quality
```bash
./vendor/bin/pint                    # Run Laravel Pint (code formatter)
php artisan route:list               # List all routes
php artisan optimize                 # Optimize application
php artisan optimize:clear           # Clear optimization cache
```

### Development Environment
- **Application Server**: Laravel Octane (high performance)
- **Frontend**: Vite + Tailwind CSS 4.0
- **Queue**: Redis with Horizon dashboard at `/horizon`
- **Debugging**: Telescope available at `/telescope`
- **Logs**: Laravel Pail for real-time log monitoring
- **API Auth**: Laravel Sanctum for token-based authentication

---

## Common Development Patterns

### Service Usage
```php
// Inject via constructor
public function __construct(
    protected ShelfTempService $service
) {}

// Call service method
$data = $this->service->getTempByShelfId($shelf_id);
```

### Eloquent Relationships
- Heavy use of `hasMany`, `belongsTo`, `belongsToMany`
- Custom scope methods for filtering
- Eager loading with `with()` for N+1 prevention

### Error Handling
- Uses custom `throwError()` helper for validation
- Wraps exceptions in try-catch with transaction rollback
- Returns JSON error responses with message

### Pagination
- Custom pagination using `LengthAwarePaginator`
- Per-page defaults: usually 15-20 items
- Handles manual pagination in service layer

---

## Testing & Quality Assurance

### Dependencies
- PHPUnit for testing
- Laravel Pint for code style
- Mockery for mocking

### Commands
- `composer test` - Run test suite
- `composer dev` - Development environment (server, queue, logs, Vite)

---

## Future Development Notes

### Areas with Complexity
1. **Auto-Ordering V2** - Recently implemented, may have edge cases
2. **MongoDB Stock Sync** - Per-branch table isolation
3. **Price Tag Analytics** - Complex grouping and filtering
4. **Telegram Notifications** - Real-time notification flow

### Potential Improvements
1. Add comprehensive API documentation
2. Implement caching for frequently accessed data
3. Add batch operations for price tag printing
4. Optimize stock sync for large datasets
5. Add webhook system for external integrations

---

## Repository Structure

```
app/
  Console/Commands/         - Artisan commands
  Http/
    Controllers/            - API endpoints (organized by domain)
    Integrations/           - External service connectors
    Middleware/             - Auth and custom middleware
    Requests/               - Form validation rules
    Resources/              - API response resources
    Telegraph/              - Telegram bot handlers
  Imports/                  - Excel import classes
  Interfaces/               - Contracts for services
  Jobs/                     - Queue jobs
  Models/                   - Eloquent models
  Services/                 - Business logic layer
database/
  factories/                - Model factories for testing
  seeders/                  - Database seeders
config/                     - Configuration files
routes/
  api.php                   - Main API routes
  web.php                   - Web routes (admin dashboard?)
```

---

## Key Metrics for Success

1. **Shelf Management**: Ability to organize and track 1000s of shelves
2. **Stock Accuracy**: Real-time stock synchronization across branches
3. **Price Tag Distribution**: Efficient printing and distribution of price tags
4. **Auto-Ordering**: Fast generation of product arrangements
5. **Notification System**: Real-time alerts to decision makers

---

Generated: 2025-11-06
