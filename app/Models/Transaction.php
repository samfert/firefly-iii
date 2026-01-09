<?php

/**
 * Transaction.php
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
use Carbon\Carbon;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Transaction
 *
 * Represents a single leg of a financial transaction in the double-entry bookkeeping system.
 * In Firefly III, every transaction journal has at least two Transaction records: one with
 * a positive amount (credit) and one with a negative amount (debit), ensuring the books
 * always balance.
 *
 * Transactions are linked to accounts and can be categorized using budgets and categories.
 * They support multiple currencies through the foreign_amount and foreign_currency_id fields,
 * allowing for currency conversion tracking.
 *
 * Key concepts:
 * - Each Transaction belongs to a TransactionJournal (the parent record)
 * - Positive amounts represent money coming into an account
 * - Negative amounts represent money leaving an account
 * - The sum of all transactions in a journal should equal zero
 *
 * @property int $id Primary key identifier
 * @property int $account_id Foreign key to the associated account
 * @property int $transaction_journal_id Foreign key to the parent transaction journal
 * @property string $description Description of this transaction leg
 * @property string $amount The transaction amount (positive or negative)
 * @property string|null $native_amount Amount in the user's native currency
 * @property string|null $foreign_amount Amount in a foreign currency
 * @property string|null $native_foreign_amount Foreign amount in native currency
 * @property int $identifier Identifier for ordering within a journal
 * @property int $transaction_currency_id Foreign key to the transaction currency
 * @property int|null $foreign_currency_id Foreign key to the foreign currency
 * @property bool $reconciled Whether this transaction has been reconciled
 * @property \Carbon\Carbon $created_at Timestamp of creation
 * @property \Carbon\Carbon $updated_at Timestamp of last update
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 */
class Transaction extends Model
{
    use HasFactory;
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable
                      = [
            'account_id',
            'transaction_journal_id',
            'description',
            'amount',
            'native_amount',
            'native_foreign_amount',
            'identifier',
            'transaction_currency_id',
            'foreign_currency_id',
            'foreign_amount',
            'reconciled',
        ];

    protected $hidden = ['encrypted'];

    /**
     * Get the account this transaction belongs to.
     *
     * Every transaction is associated with exactly one account,
     * representing either the source or destination of funds.
     *
     * @return BelongsTo Relationship to the Account model
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the budgets associated with this transaction.
     *
     * Transactions can be assigned to one or more budgets for
     * expense tracking and budget management purposes.
     *
     * @return BelongsToMany Many-to-many relationship to Budget models
     */
    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class);
    }

    /**
     * Get the categories associated with this transaction.
     *
     * Categories provide a way to classify transactions for
     * reporting and analysis purposes.
     *
     * @return BelongsToMany Many-to-many relationship to Category models
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the foreign currency for this transaction.
     *
     * Used when the transaction involves a currency different
     * from the account's primary currency.
     *
     * @return BelongsTo Relationship to the TransactionCurrency model
     */
    public function foreignCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class, 'foreign_currency_id');
    }

    /**
     * Query scope to filter transactions after a specified date.
     *
     * Joins the transaction_journals table if not already joined
     * and filters to only include transactions on or after the given date.
     *
     * @param Builder $query The query builder instance
     * @param Carbon $date The start date for filtering
     * @return void
     */
    #[Scope]
    protected function after(Builder $query, Carbon $date): void
    {
        if (!self::isJoined($query, 'transaction_journals')) {
            $query->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id');
        }
        $query->where('transaction_journals.date', '>=', $date->format('Y-m-d 00:00:00'));
    }

    /**
     * Check if a table has already been joined in the query.
     *
     * Utility method to prevent duplicate joins which would cause SQL errors.
     *
     * @param Builder $query The query builder instance
     * @param string $table The table name to check for
     * @return bool True if the table is already joined, false otherwise
     */
    public static function isJoined(Builder $query, string $table): bool
    {
        $joins = $query->getQuery()->joins;

        foreach ($joins as $join) {
            if ($join->table === $table) {
                return true;
            }
        }

        return false;
    }

    /**
     * Query scope to filter transactions before a specified date.
     *
     * Joins the transaction_journals table if not already joined
     * and filters to only include transactions on or before the given date.
     *
     * @param Builder $query The query builder instance
     * @param Carbon $date The end date for filtering
     * @return void
     */
    #[Scope]
    protected function before(Builder $query, Carbon $date): void
    {
        if (!self::isJoined($query, 'transaction_journals')) {
            $query->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id');
        }
        $query->where('transaction_journals.date', '<=', $date->format('Y-m-d 23:59:59'));
    }

    /**
     * Query scope to filter transactions by their type.
     *
     * Joins the transaction_journals and transaction_types tables if not
     * already joined and filters by the specified transaction types
     * (e.g., withdrawal, deposit, transfer).
     *
     * @param Builder $query The query builder instance
     * @param array $types Array of transaction type strings to filter by
     * @return void
     */
    #[Scope]
    protected function transactionTypes(Builder $query, array $types): void
    {
        if (!self::isJoined($query, 'transaction_journals')) {
            $query->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id');
        }

        if (!self::isJoined($query, 'transaction_types')) {
            $query->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id');
        }
        $query->whereIn('transaction_types.type', $types);
    }

    /**
     * Set the amount attribute as a string.
     *
     * Ensures amounts are stored as strings for precise decimal handling
     * and to avoid floating-point precision issues.
     *
     * @param mixed $value The amount value to set
     * @return void
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = (string) $value;
    }

    /**
     * Get the primary currency for this transaction.
     *
     * The transaction currency is the main currency in which
     * the transaction amount is denominated.
     *
     * @return BelongsTo Relationship to the TransactionCurrency model
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Get the transaction journal this transaction belongs to.
     *
     * The transaction journal is the parent record that groups
     * related transactions together in the double-entry system.
     *
     * @return BelongsTo Relationship to the TransactionJournal model
     */
    public function transactionJournal(): BelongsTo
    {
        return $this->belongsTo(TransactionJournal::class);
    }

    protected function accountId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Get the amount
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    protected function balanceDirty(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => 1 === (int) $value,
        );
    }

    /**
     * Get the foreign amount
     */
    protected function foreignAmount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    protected function transactionJournalId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    protected function casts(): array
    {
        return [
            'created_at'            => 'datetime',
            'updated_at'            => 'datetime',
            'deleted_at'            => 'datetime',
            'identifier'            => 'int',
            'encrypted'             => 'boolean', // model does not have these fields though
            'bill_name_encrypted'   => 'boolean',
            'reconciled'            => 'boolean',
            'balance_dirty'         => 'boolean',
            'balance_before'        => 'string',
            'balance_after'         => 'string',
            'date'                  => 'datetime',
            'amount'                => 'string',
            'foreign_amount'        => 'string',
            'native_amount'         => 'string',
            'native_foreign_amount' => 'string',
        ];
    }
}
