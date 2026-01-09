<?php

/**
 * Account.php
 * Copyright (c) 2019 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use FireflyIII\Enums\AccountTypeEnum;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Account
 *
 * Represents a financial account in Firefly III. Accounts are the core entity for tracking
 * money flow and can be of various types including asset accounts (checking, savings),
 * expense accounts (stores, vendors), revenue accounts (employers, clients), and
 * liability accounts (loans, mortgages, debts).
 *
 * Each account belongs to a user and has an account type that determines its behavior
 * in the double-entry bookkeeping system. Accounts can have associated metadata,
 * attachments, notes, locations, and can be grouped for organizational purposes.
 *
 * Key features:
 * - Supports virtual balance for credit card accounts
 * - Can store IBAN for European bank accounts
 * - Supports soft deletion for data preservation
 * - Can be linked to piggy banks for savings goals
 *
 * @property int $id Primary key identifier
 * @property int $user_id Foreign key to the owning user
 * @property int $user_group_id Foreign key to the user group
 * @property int $account_type_id Foreign key to the account type
 * @property string $name Display name of the account
 * @property bool $active Whether the account is currently active
 * @property string|null $virtual_balance Virtual balance for credit accounts
 * @property string|null $iban International Bank Account Number
 * @property string|null $native_virtual_balance Virtual balance in native currency
 * @property \Carbon\Carbon $created_at Timestamp of creation
 * @property \Carbon\Carbon $updated_at Timestamp of last update
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 */
class Account extends Model
{
    use HasFactory;
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable              = ['user_id', 'user_group_id', 'account_type_id', 'name', 'active', 'virtual_balance', 'iban', 'native_virtual_balance'];

    protected $hidden                = ['encrypted'];
    private bool $joinedAccountTypes = false;

    /**
     * Route binder for Laravel route model binding.
     *
     * Converts the account ID from the URL to an Account model instance.
     * Ensures the authenticated user owns the requested account for security.
     * Used by Laravel's implicit route model binding feature.
     *
     * @param string $value The account ID from the URL
     * @return self The Account model instance
     * @throws NotFoundHttpException When account is not found or user is not authenticated
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $accountId = (int) $value;

            /** @var User $user */
            $user      = auth()->user();

            /** @var null|Account $account */
            $account   = $user->accounts()->with(['accountType'])->find($accountId);
            if (null !== $account) {
                return $account;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Get the user that owns this account.
     *
     * @return BelongsTo Relationship to the User model
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all balance records for this account.
     *
     * Account balances track the historical balance of the account
     * at different points in time for reporting and reconciliation.
     *
     * @return HasMany Relationship to AccountBalance models
     */
    public function accountBalances(): HasMany
    {
        return $this->hasMany(AccountBalance::class);
    }

    /**
     * Get the type of this account.
     *
     * Account types define the behavior and categorization of accounts
     * (e.g., asset, expense, revenue, liability).
     *
     * @return BelongsTo Relationship to the AccountType model
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get all attachments associated with this account.
     *
     * Attachments can include documents, receipts, or other files
     * related to the account.
     *
     * @return MorphMany Polymorphic relationship to Attachment models
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get the account number.
     */
    protected function accountNumber(): Attribute
    {
        return Attribute::make(get: function () {
            /** @var null|AccountMeta $metaValue */
            $metaValue = $this->accountMeta()
                ->where('name', 'account_number')
                ->first()
            ;

            return null !== $metaValue ? $metaValue->data : '';
        });
    }

    /**
     * Get all metadata associated with this account.
     *
     * Account metadata stores additional key-value pairs such as
     * account numbers, BIC codes, interest rates, and other
     * account-specific information.
     *
     * @return HasMany Relationship to AccountMeta models
     */
    public function accountMeta(): HasMany
    {
        return $this->hasMany(AccountMeta::class);
    }

    /**
     * Get the editable name of the account.
     *
     * Returns an empty string for cash accounts since they
     * don't have editable names. For all other account types,
     * returns the account name.
     *
     * @return Attribute Accessor for the edit name
     */
    protected function editName(): Attribute
    {
        return Attribute::make(get: function () {
            $name = $this->name;
            if (AccountTypeEnum::CASH->value === $this->accountType->type) {
                return '';
            }

            return $name;
        });
    }

    /**
     * Get all geographic locations associated with this account.
     *
     * Locations can be used to track where transactions occur
     * or where the account is physically located.
     *
     * @return MorphMany Polymorphic relationship to Location models
     */
    public function locations(): MorphMany
    {
        return $this->morphMany(Location::class, 'locatable');
    }

    /**
     * Get all notes associated with this account.
     *
     * Notes allow users to add free-form text descriptions
     * or reminders about the account.
     *
     * @return MorphMany Polymorphic relationship to Note models
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Get all object groups this account belongs to.
     *
     * Object groups allow users to organize accounts into
     * custom categories for better organization and filtering.
     *
     * @return MorphToMany Polymorphic many-to-many relationship to ObjectGroup models
     */
    public function objectGroups(): MorphToMany
    {
        return $this->morphToMany(ObjectGroup::class, 'object_groupable');
    }

    /**
     * Get all piggy banks linked to this account.
     *
     * Piggy banks are savings goals that can be funded from
     * asset accounts. Multiple piggy banks can be linked to
     * a single account.
     *
     * @return BelongsToMany Many-to-many relationship to PiggyBank models
     */
    public function piggyBanks(): BelongsToMany
    {
        return $this->belongsToMany(PiggyBank::class);
    }

    /**
     * Query scope to filter accounts by their type.
     *
     * Joins the account_types table if not already joined and filters
     * accounts to only include those matching the specified types.
     *
     * @param EloquentBuilder $query The query builder instance
     * @param array $types Array of account type strings to filter by
     * @return void
     */
    #[Scope]
    protected function accountTypeIn(EloquentBuilder $query, array $types): void
    {
        if (false === $this->joinedAccountTypes) {
            $query->leftJoin('account_types', 'account_types.id', '=', 'accounts.account_type_id');
            $this->joinedAccountTypes = true;
        }
        $query->whereIn('account_types.type', $types);
    }

    /**
     * Set the virtual balance attribute.
     *
     * Converts the value to string and sets to null if empty.
     * Virtual balance is used for credit card accounts to track
     * the credit limit or expected balance.
     *
     * @param mixed $value The virtual balance value to set
     * @return void
     */
    public function setVirtualBalanceAttribute(mixed $value): void
    {
        $value                               = (string) $value;
        if ('' === $value) {
            $value = null;
        }
        $this->attributes['virtual_balance'] = $value;
    }

    /**
     * Get all transactions associated with this account.
     *
     * Transactions represent individual money movements in or out
     * of this account as part of the double-entry bookkeeping system.
     *
     * @return HasMany Relationship to Transaction models
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user group this account belongs to.
     *
     * User groups allow multiple users to share access to accounts
     * and financial data for household or team budgeting.
     *
     * @return BelongsTo Relationship to the UserGroup model
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Get the account ID as an integer.
     *
     * Ensures the account ID is always returned as an integer type
     * for consistent type handling throughout the application.
     *
     * @return Attribute Accessor for the account ID
     */
    protected function accountId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Get the account type ID as an integer.
     *
     * Ensures the account type ID is always returned as an integer type
     * for consistent type handling throughout the application.
     *
     * @return Attribute Accessor for the account type ID
     */
    protected function accountTypeId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Get the IBAN with spaces removed.
     *
     * Normalizes the IBAN by removing all spaces for consistent
     * storage and comparison. Returns null if no IBAN is set.
     *
     * @return Attribute Accessor for the IBAN
     */
    protected function iban(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => null === $value ? null : trim(str_replace(' ', '', (string) $value)),
        );
    }

    /**
     * Get the display order as an integer.
     *
     * The order determines how accounts are sorted in lists
     * and can be customized by the user.
     *
     * @return Attribute Accessor for the order
     */
    protected function order(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Get the virtual balance as a string.
     *
     * Virtual balance is used for credit card accounts to represent
     * the credit limit or expected balance. Stored as string for
     * precise decimal handling.
     *
     * @return Attribute Accessor for the virtual balance
     */
    protected function virtualBalance(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Define the attribute casting for this model.
     *
     * Specifies how database columns should be cast to PHP types
     * when retrieved from the database.
     *
     * @return array<string, string> Array of attribute names to cast types
     */
    protected function casts(): array
    {
        return [
            'created_at'             => 'datetime',
            'updated_at'             => 'datetime',
            'user_id'                => 'integer',
            'user_group_id'          => 'integer',
            'deleted_at'             => 'datetime',
            'active'                 => 'boolean',
            'encrypted'              => 'boolean',
            'virtual_balance'        => 'string',
            'native_virtual_balance' => 'string',
        ];
    }
}
